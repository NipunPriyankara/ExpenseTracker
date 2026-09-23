<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $tab = $request->query('tab', 'all');

        $query = Loan::with('payments')
            ->where('user_id', $userId);

        if ($tab === 'borrowed') {
            $query->where('type', 'borrowed')->where('status', 'active');
        } elseif ($tab === 'lent') {
            $query->where('type', 'lent')->where('status', 'active');
        } elseif ($tab === 'settled') {
            $query->where('status', 'settled');
        }

        $loans = $query->latest('id')->get();

        // Calculate all overall statistics across all user loans
        $allLoans = Loan::where('user_id', $userId)->get();

        // Borrowed metrics (Payable / Debt)
        $borrowedLoans = $allLoans->where('type', 'borrowed');
        $totalBorrowed = $borrowedLoans->sum('total_amount');
        $totalRepaidBorrowed = $borrowedLoans->sum('paid_amount');
        $remainingDebt = max(0, $totalBorrowed - $totalRepaidBorrowed);

        // Lent metrics (Receivable / Given)
        $lentLoans = $allLoans->where('type', 'lent');
        $totalLent = $lentLoans->sum('total_amount');
        $totalCollectedLent = $lentLoans->sum('paid_amount');
        $remainingReceivable = max(0, $totalLent - $totalCollectedLent);

        // Counts
        $countActiveBorrowed = $borrowedLoans->where('status', 'active')->count();
        $countActiveLent = $lentLoans->where('status', 'active')->count();
        $countSettled = $allLoans->where('status', 'settled')->count();

        return view('loans.index', compact(
            'loans',
            'tab',
            'totalBorrowed',
            'totalRepaidBorrowed',
            'remainingDebt',
            'totalLent',
            'totalCollectedLent',
            'remainingReceivable',
            'countActiveBorrowed',
            'countActiveLent',
            'countSettled'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:borrowed,lent'],
            'person_name' => ['required', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:150'],
            'total_amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'monthly_installment' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $name = !empty($validated['name']) 
            ? trim($validated['name']) 
            : ($validated['type'] === 'borrowed' ? 'Loan from ' . trim($validated['person_name']) : 'Loan to ' . trim($validated['person_name']));

        Loan::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'person_name' => trim($validated['person_name']),
            'name' => $name,
            'total_amount' => $validated['total_amount'],
            'monthly_installment' => $validated['monthly_installment'] ?? null,
            'interest_rate' => $validated['interest_rate'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'paid_amount' => 0.00,
            'status' => 'active',
            'notes' => $validated['notes'] ? trim($validated['notes']) : null,
        ]);

        $typeLabel = $validated['type'] === 'borrowed' ? 'Borrowed loan' : 'Lent money';
        return redirect()->route('loans.index', ['tab' => $validated['type']])
            ->with('success', "{$typeLabel} for \"{$validated['person_name']}\" recorded successfully!");
    }

    public function recordPayment(Request $request, Loan $loan)
    {
        if ($loan->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'payment_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
            'record_transaction' => ['nullable', 'boolean'],
        ]);

        $amount = (float)$validated['amount'];

        // Record loan payment entry with date and note
        LoanPayment::create([
            'loan_id' => $loan->id,
            'user_id' => Auth::id(),
            'amount' => $amount,
            'payment_date' => $validated['payment_date'],
            'note' => $validated['note'] ? trim($validated['note']) : 'Payment / Settlement',
        ]);

        // Increment paid amount
        $newPaidAmount = round((float)$loan->paid_amount + $amount, 2);
        $loan->paid_amount = $newPaidAmount;

        // Auto mark as settled if full amount or more is paid
        if ($loan->remaining_amount <= 0.00) {
            $loan->status = 'settled';
        }
        $loan->save();

        // Global Accounting Standards Alignment (GAAP/IFRS):
        // 1. Lent Loan Settlement: Asset Recovery (Cash Inflow).
        //    Never record into Income to prevent double-counting of user's earnings.
        // 2. Borrowed Loan Repayment: Liability Reduction.
        //    Optionally record as cash-outflow Debt Repayment if requested.
        if ($loan->isBorrowed() && $request->boolean('record_transaction')) {
            $loanCategory = Category::firstOrCreate(
                ['user_id' => Auth::id(), 'name' => 'Debt Repayment'],
                ['color' => '#f59e0b', 'icon' => 'bi-bank']
            );

            Expense::create([
                'user_id' => Auth::id(),
                'category_id' => $loanCategory->id,
                'amount' => $amount,
                'date' => $validated['payment_date'],
                'payment_method' => 'Cash',
                'note' => "Repayment for {$loan->name} to {$loan->person_name}" . ($validated['note'] ? " ({$validated['note']})" : ''),
            ]);
        }

        $formattedAmount = number_format($amount, 2);
        $statusMsg = $loan->isSettled() ? " and this loan is now FULLY SETTLED!" : ". Remaining: Rs. " . number_format($loan->remaining_amount, 2);

        $noticeMsg = $loan->isLent() 
            ? "Settlement of Rs. {$formattedAmount} recorded successfully (Capital recovered - earned income preserved){$statusMsg}"
            : "Payment of Rs. {$formattedAmount} recorded successfully{$statusMsg}";

        return redirect()->back()->with('success', $noticeMsg);
    }

    public function update(Request $request, Loan $loan)
    {
        if ($loan->user_id !== Auth::id()) {
            abort(403);
        }

        $minAmount = max(0.01, (float)$loan->paid_amount);

        $validated = $request->validate([
            'type' => ['required', 'in:borrowed,lent'],
            'person_name' => ['required', 'string', 'max:100'],
            'name' => ['nullable', 'string', 'max:150'],
            'total_amount' => ['required', 'numeric', "min:{$minAmount}", 'max:999999999.99'],
            'monthly_installment' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $name = !empty($validated['name']) 
            ? trim($validated['name']) 
            : ($validated['type'] === 'borrowed' ? 'Loan from ' . trim($validated['person_name']) : 'Loan to ' . trim($validated['person_name']));

        $totalAmount = (float)$validated['total_amount'];
        $paidAmount = (float)$loan->paid_amount;
        $status = ($paidAmount >= $totalAmount) ? 'settled' : 'active';

        $loan->update([
            'type' => $validated['type'],
            'person_name' => trim($validated['person_name']),
            'name' => $name,
            'total_amount' => $totalAmount,
            'monthly_installment' => $validated['monthly_installment'] ?? null,
            'interest_rate' => $validated['interest_rate'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ? trim($validated['notes']) : null,
            'status' => $status,
        ]);

        return redirect()->back()
            ->with('success', "Loan details for \"{$validated['person_name']}\" updated successfully!");
    }

    public function deletePayment(LoanPayment $payment)
    {
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        $loan = $payment->loan;
        $amount = (float)$payment->amount;

        $payment->delete();

        // Recalculate loan paid amount
        $loan->paid_amount = max(0, round((float)$loan->paid_amount - $amount, 2));
        if ($loan->remaining_amount > 0.00) {
            $loan->status = 'active';
        }
        $loan->save();

        return redirect()->back()->with('success', 'Payment entry removed. Loan balance updated.');
    }

    public function destroy(Loan $loan)
    {
        if ($loan->user_id !== Auth::id()) {
            abort(403);
        }

        $loan->delete();

        return redirect()->route('loans.index')->with('success', 'Loan record removed.');
    }
}
