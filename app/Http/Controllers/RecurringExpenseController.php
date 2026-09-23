<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\RecurringExpense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurringExpenseController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $recurringExpenses = RecurringExpense::with('category')
            ->where('user_id', $userId)
            ->orderBy('is_active', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        $categories = Category::forUser($userId)->where('type', 'expense')->orderBy('name')->get();

        $totalMonthlyCommitment = $recurringExpenses->where('is_active', true)->sum(function ($item) {
            if ($item->frequency === 'weekly') return $item->amount * 4.33;
            if ($item->frequency === 'yearly') return $item->amount / 12;
            return $item->amount;
        });

        return view('recurring.index', compact('recurringExpenses', 'categories', 'totalMonthlyCommitment'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'category_id' => ['required', 'exists:categories,id'],
            'frequency' => ['required', 'in:weekly,monthly,yearly'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'payment_method' => ['required', 'string', 'in:Cash,Bank,Debit Card,Credit Card,Online Payment,Other'],
        ]);

        RecurringExpense::create([
            'user_id' => Auth::id(),
            'name' => trim($validated['name']),
            'amount' => $validated['amount'],
            'category_id' => $validated['category_id'],
            'frequency' => $validated['frequency'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'payment_method' => $validated['payment_method'],
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Recurring expense "' . $validated['name'] . '" added successfully!');
    }

    public function toggle(RecurringExpense $recurringExpense)
    {
        if ($recurringExpense->user_id !== Auth::id()) {
            abort(403);
        }

        $recurringExpense->is_active = !$recurringExpense->is_active;
        $recurringExpense->save();

        return redirect()->back()->with('success', 'Status updated for "' . $recurringExpense->name . '".');
    }

    public function recordNow(RecurringExpense $recurringExpense)
    {
        if ($recurringExpense->user_id !== Auth::id()) {
            abort(403);
        }

        Expense::create([
            'user_id' => Auth::id(),
            'category_id' => $recurringExpense->category_id,
            'amount' => $recurringExpense->amount,
            'date' => Carbon::today(),
            'payment_method' => $recurringExpense->payment_method,
            'note' => 'Recurring: ' . $recurringExpense->name,
        ]);

        $recurringExpense->update(['last_generated_date' => Carbon::today()]);

        return redirect()->route('expenses.index')->with('success', 'Expense for "' . $recurringExpense->name . '" (Rs. ' . number_format($recurringExpense->amount, 2) . ') recorded successfully!');
    }

    public function destroy(RecurringExpense $recurringExpense)
    {
        if ($recurringExpense->user_id !== Auth::id()) {
            abort(403);
        }

        $recurringExpense->delete();

        return redirect()->back()->with('success', 'Recurring expense deleted.');
    }
}
