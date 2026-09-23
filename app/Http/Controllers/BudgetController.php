<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));

        $monthDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $startOfMonth = $monthDate->copy()->startOfMonth();
        $endOfMonth = $monthDate->copy()->endOfMonth();

        // Get overall budget
        $budget = Budget::with('categories')
            ->where('user_id', $userId)
            ->where('month', $selectedMonth)
            ->first();

        $overallBudgetAmount = $budget ? (float)$budget->amount : 0.00;

        // Total spent this month
        $totalSpent = (float)Expense::where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $remainingBudget = $overallBudgetAmount > 0 ? ($overallBudgetAmount - $totalSpent) : 0.00;
        $usagePercent = $overallBudgetAmount > 0 ? round(($totalSpent / $overallBudgetAmount) * 100, 1) : 0;

        // Category-wise spending & budget limits
        $categories = Category::forUser($userId)
            ->where('type', 'expense')
            ->with(['parent', 'subcategories'])
            ->orderBy('name')
            ->get();

        $categoryBudgets = [];
        if ($budget) {
            foreach ($budget->categories as $bCat) {
                $categoryBudgets[$bCat->category_id] = (float)$bCat->amount;
            }
        }

        $categoryStats = $categories->map(function ($cat) use ($userId, $startOfMonth, $endOfMonth, $categoryBudgets) {
            $spent = (float)Expense::where('user_id', $userId)
                ->where('category_id', $cat->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $allocatedBudget = $categoryBudgets[$cat->id] ?? 0.00;
            $remaining = $allocatedBudget > 0 ? ($allocatedBudget - $spent) : null;
            $catUsagePercent = $allocatedBudget > 0 ? round(($spent / $allocatedBudget) * 100, 1) : 0;

            return [
                'category' => $cat,
                'allocated' => $allocatedBudget,
                'spent' => $spent,
                'remaining' => $remaining,
                'usage_percent' => $catUsagePercent,
                'is_over_budget' => $allocatedBudget > 0 && $spent > $allocatedBudget,
            ];
        });

        return view('budgets.index', compact(
            'selectedMonth',
            'overallBudgetAmount',
            'totalSpent',
            'remainingBudget',
            'usagePercent',
            'categoryStats',
            'categories',
            'categoryBudgets'
        ));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'category_budgets' => ['nullable', 'array'],
            'category_budgets.*' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
        ]);

        $budget = Budget::updateOrCreate(
            ['user_id' => $userId, 'month' => $validated['month']],
            ['amount' => $validated['amount']]
        );

        if (!empty($validated['category_budgets'])) {
            foreach ($validated['category_budgets'] as $categoryId => $catAmount) {
                if ($catAmount !== null && $catAmount !== '' && $catAmount > 0) {
                    BudgetCategory::updateOrCreate(
                        ['budget_id' => $budget->id, 'category_id' => $categoryId],
                        ['amount' => $catAmount]
                    );
                } else {
                    BudgetCategory::where('budget_id', $budget->id)
                        ->where('category_id', $categoryId)
                        ->delete();
                }
            }
        }

        return redirect()->route('budgets.index', ['month' => $validated['month']])
            ->with('success', 'Budget for ' . Carbon::createFromFormat('Y-m', $validated['month'])->format('F Y') . ' saved successfully!');
    }
}
