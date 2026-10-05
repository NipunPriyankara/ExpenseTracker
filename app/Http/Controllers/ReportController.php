<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Period filter (default current month)
        $filter = $request->input('period', 'current_month');
        $customStart = $request->input('start_date');
        $customEnd = $request->input('end_date');

        $now = Carbon::now();
        if ($filter === 'previous_month') {
            $startDate = $now->copy()->subMonth()->startOfMonth();
            $endDate = $now->copy()->subMonth()->endOfMonth();
        } elseif ($filter === 'all_time') {
            $earliestExp = Expense::where('user_id', $userId)->min('date');
            $earliestInc = Income::where('user_id', $userId)->min('date');
            $minDateStr = collect([$earliestExp, $earliestInc])->filter()->min();
            $startDate = $minDateStr ? Carbon::parse($minDateStr)->startOfDay() : $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfDay();
        } elseif ($filter === 'custom' && $customStart && $customEnd) {
            $startDate = Carbon::parse($customStart)->startOfDay();
            $endDate = Carbon::parse($customEnd)->endOfDay();
        } else {
            $filter = 'current_month';
            $startDate = $now->copy()->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        }

        // Summary metrics
        $totalIncome = (float)Income::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $totalExpense = (float)Expense::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $netBalance = $totalIncome - $totalExpense;

        $incomeCount = Income::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        $expenseCount = Expense::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        $totalTransactions = $incomeCount + $expenseCount;

        // 1. Category Breakdown (for Doughnut Chart & Table)
        $expensesByCategory = Expense::with('category')
            ->where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('category_id')
            ->map(function ($items) use ($totalExpense) {
                $category = $items->first()->category;
                $sum = $items->sum('amount');
                $percent = $totalExpense > 0 ? round(($sum / $totalExpense) * 100, 1) : 0;
                return [
                    'name' => $category ? $category->name : 'Uncategorized',
                    'color' => $category ? $category->color : '#94a3b8',
                    'icon' => $category ? $category->icon : 'bi-tag',
                    'amount' => $sum,
                    'percentage' => $percent,
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('amount')
            ->values();

        // 2. Daily Expenses (Line Chart)
        $daysInPeriod = [];
        $dailyExpenseData = [];

        // For all_time or ranges > 30 days, plot the last 30 days for daily flow
        $diffInDays = $startDate->diffInDays($endDate);
        $dailyStart = ($filter === 'all_time' && $diffInDays > 30) 
            ? $now->copy()->subDays(29)->startOfDay() 
            : $startDate->copy();

        $currentDay = $dailyStart->copy();
        while ($currentDay->lte($endDate) && count($daysInPeriod) < 31) {
            $dateStr = $currentDay->format('Y-m-d');
            $daysInPeriod[] = $currentDay->format('M d');

            $daySum = (float)Expense::where('user_id', $userId)
                ->whereDate('date', $dateStr)
                ->sum('amount');

            $dailyExpenseData[] = $daySum;
            $currentDay->addDay();
        }

        // 3. Monthly Trends (Last 6-12 Months: Income vs Expense)
        $monthsTrend = [];
        $monthlyIncomeData = [];
        $monthlyExpenseData = [];

        $trendMonthsCount = $filter === 'all_time' ? 12 : 6;

        for ($i = $trendMonthsCount - 1; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $mStart = $m->copy()->startOfMonth();
            $mEnd = $m->copy()->endOfMonth();

            $monthsTrend[] = $m->format('M Y');

            $mIncome = (float)Income::where('user_id', $userId)
                ->whereBetween('date', [$mStart, $mEnd])
                ->sum('amount');

            $mExpense = (float)Expense::where('user_id', $userId)
                ->whereBetween('date', [$mStart, $mEnd])
                ->sum('amount');

            $monthlyIncomeData[] = $mIncome;
            $monthlyExpenseData[] = $mExpense;
        }

        return view('reports.index', compact(
            'filter',
            'startDate',
            'endDate',
            'totalIncome',
            'totalExpense',
            'netBalance',
            'totalTransactions',
            'expensesByCategory',
            'daysInPeriod',
            'dailyExpenseData',
            'monthsTrend',
            'monthlyIncomeData',
            'monthlyExpenseData'
        ));
    }
}
