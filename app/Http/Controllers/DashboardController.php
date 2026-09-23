<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Loan;
use App\Models\LoanPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $startOfMonth = $monthDate->copy()->startOfMonth();
        $endOfMonth = $monthDate->copy()->endOfMonth();

        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();

        // Calculations strictly scoped to authenticated user (Pure Operating P&L)
        $currentMonthIncome = Income::where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $currentMonthExpense = Expense::where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $currentMonthBalance = $currentMonthIncome - $currentMonthExpense;

        $todayExpense = Expense::where('user_id', $userId)
            ->whereDate('date', $today)
            ->sum('amount');

        $thisWeekExpense = Expense::where('user_id', $userId)
            ->whereBetween('date', [$startOfWeek, Carbon::now()->endOfWeek()])
            ->sum('amount');

        $budget = Budget::where('user_id', $userId)
            ->where('month', $selectedMonth)
            ->first();

        $monthlyBudget = $budget ? (float)$budget->amount : 0.00;
        $remainingBudget = $monthlyBudget > 0 ? ($monthlyBudget - $currentMonthExpense) : 0.00;
        $budgetUsagePercent = $monthlyBudget > 0 ? round(($currentMonthExpense / $monthlyBudget) * 100, 1) : 0;

        // Balance Sheet Metrics (Assets: Receivables vs Liabilities: Debt)
        $activeLentLoans = Loan::where('user_id', $userId)->where('type', 'lent')->where('status', 'active')->get();
        $totalLentReceivable = $activeLentLoans->sum(fn($l) => $l->remaining_amount);

        $activeBorrowedLoans = Loan::where('user_id', $userId)->where('type', 'borrowed')->where('status', 'active')->get();
        $totalDebtPayable = $activeBorrowedLoans->sum(fn($l) => $l->remaining_amount);

        // Recent 5 transactions (expenses + incomes + settlements)
        $rawRecentExpenses = Expense::with(['category.parent'])
            ->where('user_id', $userId)
            ->latest('date')
            ->latest('id')
            ->take(15)
            ->get();

        $recentExpenses = $rawRecentExpenses->groupBy(function ($item) {
            return $item->split_group_id ?: ('single_' . $item->id);
        })->map(function ($items, $groupKey) {
            $first = $items->first();
            $isSplit = str_starts_with($groupKey, 'split_') && $items->count() > 1;

            if ($isSplit) {
                return [
                    'id' => $first->id,
                    'type' => 'expense',
                    'is_split' => true,
                    'title' => 'Split Bill (' . $items->count() . ' items)',
                    'icon' => 'bi-receipt-cutoff',
                    'color' => '#4f46e5',
                    'amount' => $items->sum('amount'),
                    'date' => $first->date->format('M d, Y'),
                    'payment_method' => $first->payment_method,
                    'transport_mode' => null,
                    'vehicle_type' => null,
                    'note' => $items->map(fn($it) => ($it->category ? $it->category->display_name : '') . ': Rs. ' . number_format($it->amount, 2))->implode(' | '),
                ];
            }

            return [
                'id' => $first->id,
                'type' => 'expense',
                'is_split' => false,
                'title' => $first->category ? $first->category->display_name : 'Expense',
                'icon' => $first->category ? $first->category->icon : 'bi-credit-card',
                'color' => $first->category ? $first->category->color : '#ef4444',
                'amount' => $first->amount,
                'date' => $first->date->format('M d, Y'),
                'payment_method' => $first->payment_method,
                'transport_mode' => $first->transport_mode,
                'vehicle_type' => $first->vehicle_type,
                'note' => $first->note,
            ];
        })->values();

        $rawRecentIncomes = Income::with('category.parent')
            ->where('user_id', $userId)
            ->latest('date')
            ->latest('id')
            ->take(15)
            ->get();

        $recentIncomes = $rawRecentIncomes->groupBy(function ($item) {
            return $item->split_group_id ?: ('single_' . $item->id);
        })->map(function ($items, $groupKey) {
            $first = $items->first();
            $isSplit = str_starts_with($groupKey, 'split_inc_') && $items->count() > 1;

            if ($isSplit) {
                return [
                    'id' => $first->id,
                    'type' => 'income',
                    'is_split' => true,
                    'title' => 'Split Income (' . $items->count() . ' items)',
                    'icon' => 'bi-receipt-cutoff',
                    'color' => '#10b981',
                    'amount' => $items->sum('amount'),
                    'date' => $first->date->format('M d, Y'),
                    'payment_method' => 'Income Deposit',
                    'note' => $items->map(fn($it) => ($it->category ? $it->category->display_name : ($it->source ?: 'Item')) . ': Rs. ' . number_format($it->amount, 2))->implode(' | '),
                ];
            }

            return [
                'id' => $first->id,
                'type' => 'income',
                'is_split' => false,
                'title' => $first->category ? $first->category->display_name : ($first->source ?: 'Income'),
                'icon' => $first->category ? $first->category->icon : 'bi-cash-coin',
                'color' => $first->category ? $first->category->color : '#10b981',
                'amount' => $first->amount,
                'date' => $first->date->format('M d, Y'),
                'payment_method' => 'Income',
                'note' => $first->note,
            ];
        })->values()->take(5);

        $recentLoanPayments = LoanPayment::with('loan')
            ->where('user_id', $userId)
            ->latest('payment_date')
            ->latest('id')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $isLent = $item->loan && $item->loan->isLent();
                return [
                    'id' => $item->id,
                    'type' => 'settlement',
                    'is_inflow' => $isLent,
                    'title' => $isLent ? 'Loan Settlement (' . ($item->loan ? $item->loan->person_name : 'Lent') . ')' : 'Debt Repayment (' . ($item->loan ? $item->loan->person_name : 'Debt') . ')',
                    'icon' => $isLent ? 'bi-arrow-down-left-circle-fill' : 'bi-arrow-up-right-circle-fill',
                    'color' => $isLent ? '#0284c7' : '#f59e0b',
                    'amount' => $item->amount,
                    'date' => $item->payment_date->format('M d, Y'),
                    'payment_method' => 'Loan Settlement',
                    'note' => $item->note,
                ];
            });

        $recentTransactions = collect($recentExpenses)->merge($recentIncomes)->merge($recentLoanPayments)
            ->sortByDesc(function ($tx) {
                return Carbon::parse($tx['date'])->timestamp;
            })
            ->take(6)
            ->values();

        $categories = Category::forUser($userId)->where('type', 'expense')->get();

        return view('dashboard', compact(
            'selectedMonth',
            'currentMonthIncome',
            'currentMonthExpense',
            'currentMonthBalance',
            'todayExpense',
            'thisWeekExpense',
            'monthlyBudget',
            'remainingBudget',
            'budgetUsagePercent',
            'totalLentReceivable',
            'totalDebtPayable',
            'recentTransactions',
            'categories'
        ));
    }
}
