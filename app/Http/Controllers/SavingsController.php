<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\SavingsGoal;
use App\Models\SavingsTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingsController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $statusFilter = $request->query('status', 'all');

        $query = SavingsGoal::with('transactions')
            ->where('user_id', $userId);

        if ($statusFilter === 'active') {
            $query->where('status', 'active');
        } elseif ($statusFilter === 'completed') {
            $query->where('status', 'completed');
        }

        $goals = $query->latest('id')->get();

        // Calculate all overall statistics across all user goals
        $allGoals = SavingsGoal::where('user_id', $userId)->get();

        $totalSaved = $allGoals->sum('current_amount');
        $totalTarget = $allGoals->sum('target_amount');
        $overallProgress = $totalTarget > 0 ? min(100, round(($totalSaved / $totalTarget) * 100, 1)) : 0;

        $countActive = $allGoals->where('status', 'active')->count();
        $countCompleted = $allGoals->where('status', 'completed')->count();

        return view('savings.index', compact(
            'goals',
            'statusFilter',
            'totalSaved',
            'totalTarget',
            'overallProgress',
            'countActive',
            'countCompleted'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'target_amount' => ['required', 'numeric', 'min:1', 'max:999999999.99'],
            'initial_deposit' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'target_date' => ['nullable', 'date', 'after_or_equal:today'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $initialDeposit = (float)($validated['initial_deposit'] ?? 0);
        $targetAmount = (float)$validated['target_amount'];

        $goal = SavingsGoal::create([
            'user_id' => Auth::id(),
            'name' => trim($validated['name']),
            'target_amount' => $targetAmount,
            'current_amount' => $initialDeposit,
            'target_date' => $validated['target_date'] ?? null,
            'icon' => $validated['icon'] ?: 'bi-piggy-bank',
            'color' => $validated['color'] ?: '#10b981',
            'status' => ($initialDeposit >= $targetAmount) ? 'completed' : 'active',
            'notes' => !empty($validated['notes']) ? trim($validated['notes']) : null,
        ]);

        // Record initial deposit transaction if provided
        if ($initialDeposit > 0) {
            SavingsTransaction::create([
                'savings_goal_id' => $goal->id,
                'user_id' => Auth::id(),
                'type' => 'deposit',
                'amount' => $initialDeposit,
                'transaction_date' => date('Y-m-d'),
                'note' => 'Initial Deposit',
            ]);
        }

        return redirect()->route('savings.index')->with('success', "Savings Goal \"{$goal->name}\" created successfully!");
    }

    public function deposit(Request $request, SavingsGoal $savingsGoal)
    {
        if ($savingsGoal->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'transaction_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'record_as_expense' => ['nullable', 'boolean'],
        ]);

        $amount = (float)$validated['amount'];

        // Record deposit transaction
        SavingsTransaction::create([
            'savings_goal_id' => $savingsGoal->id,
            'user_id' => Auth::id(),
            'type' => 'deposit',
            'amount' => $amount,
            'transaction_date' => $validated['transaction_date'],
            'note' => !empty($validated['note']) ? trim($validated['note']) : 'Savings Deposit',
        ]);

        // Increment current amount
        $newAmount = round((float)$savingsGoal->current_amount + $amount, 2);
        $savingsGoal->current_amount = $newAmount;

        // Auto complete if target reached
        if ($savingsGoal->current_amount >= $savingsGoal->target_amount) {
            $savingsGoal->status = 'completed';
        }
        $savingsGoal->save();

        // Optional sync to Expenses (money put into savings fund)
        if ($request->boolean('record_as_expense')) {
            $savingsCategory = Category::firstOrCreate(
                ['user_id' => Auth::id(), 'name' => 'Savings Transfer'],
                ['color' => '#10b981', 'icon' => 'bi-piggy-bank']
            );

            Expense::create([
                'user_id' => Auth::id(),
                'category_id' => $savingsCategory->id,
                'amount' => $amount,
                'date' => $validated['transaction_date'],
                'payment_method' => 'Bank',
                'note' => "Transfer to Savings Goal: {$savingsGoal->name}" . ($validated['note'] ? " ({$validated['note']})" : ''),
            ]);
        }

        $formatted = number_format($amount, 2);
        $statusMsg = $savingsGoal->isCompleted() ? " 🎉 Target reached! Goal marked as completed!" : ". Current balance: Rs. " . number_format($savingsGoal->current_amount, 2);

        return redirect()->back()->with('success', "Deposited Rs. {$formatted} into {$savingsGoal->name}{$statusMsg}");
    }

    public function withdraw(Request $request, SavingsGoal $savingsGoal)
    {
        if ($savingsGoal->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', "max:{$savingsGoal->current_amount}"],
            'transaction_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'record_as_income' => ['nullable', 'boolean'],
        ]);

        $amount = (float)$validated['amount'];

        // Record withdrawal transaction
        SavingsTransaction::create([
            'savings_goal_id' => $savingsGoal->id,
            'user_id' => Auth::id(),
            'type' => 'withdraw',
            'amount' => $amount,
            'transaction_date' => $validated['transaction_date'],
            'note' => !empty($validated['note']) ? trim($validated['note']) : 'Savings Withdrawal',
        ]);

        // Decrement current amount
        $newAmount = max(0, round((float)$savingsGoal->current_amount - $amount, 2));
        $savingsGoal->current_amount = $newAmount;

        // If it was completed and now below target, reactivate
        if ($savingsGoal->status === 'completed' && $savingsGoal->current_amount < $savingsGoal->target_amount) {
            $savingsGoal->status = 'active';
        }
        $savingsGoal->save();

        // Optional sync to Income (money withdrawn back into wallet/bank)
        if ($request->boolean('record_as_income')) {
            Income::create([
                'user_id' => Auth::id(),
                'source' => "Savings Withdrawal - {$savingsGoal->name}",
                'amount' => $amount,
                'date' => $validated['transaction_date'],
                'notes' => "Withdrawn from {$savingsGoal->name}" . ($validated['note'] ? " ({$validated['note']})" : ''),
            ]);
        }

        $formatted = number_format($amount, 2);
        return redirect()->back()->with('success', "Withdrew Rs. {$formatted} from {$savingsGoal->name}. Remaining balance: Rs. " . number_format($savingsGoal->current_amount, 2));
    }

    public function destroy(SavingsGoal $savingsGoal)
    {
        if ($savingsGoal->user_id !== Auth::id()) {
            abort(403);
        }

        $name = $savingsGoal->name;
        $savingsGoal->delete();

        return redirect()->route('savings.index')->with('success', "Savings Goal \"{$name}\" removed.");
    }
}
