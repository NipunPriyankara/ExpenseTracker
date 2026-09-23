<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\RecurringExpense;
use App\Models\SavingsGoal;
use App\Models\SavingsTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    /**
     * Admin dashboard & user listing
     */
    public function index(Request $request)
    {
        // System-wide metrics
        $totalUsers = User::count();
        $totalAdmins = User::where('is_admin', true)->count();
        $totalExpensesCount = Expense::count();
        $totalExpensesAmount = (float) Expense::sum('amount');
        $totalIncomesCount = Income::count();
        $totalIncomesAmount = (float) Income::sum('amount');
        $totalActiveLoans = Loan::where('status', 'active')->count();

        // Users Query
        $query = User::withCount(['expenses', 'incomes', 'loans', 'budgets'])
            ->withSum('expenses', 'amount')
            ->withSum('incomes', 'amount');

        // Search by name or email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        $role = $request->input('role');
        if ($role === 'admin') {
            $query->where('is_admin', true);
        } elseif ($role === 'user') {
            $query->where('is_admin', false);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('name', 'desc');
        } elseif ($sort === 'most_expenses') {
            $query->orderByDesc('expenses_count');
        } elseif ($sort === 'most_incomes') {
            $query->orderByDesc('incomes_count');
        } else {
            $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        }

        $users = $query->paginate(12)->withQueryString();

        return view('admin.index', compact(
            'users',
            'totalUsers',
            'totalAdmins',
            'totalExpensesCount',
            'totalExpensesAmount',
            'totalIncomesCount',
            'totalIncomesAmount',
            'totalActiveLoans'
        ));
    }

    /**
     * Get detailed statistics and activity for a specific user (AJAX endpoint)
     */
    public function show(User $user)
    {
        $expensesCount = Expense::where('user_id', $user->id)->count();
        $expensesTotal = (float) Expense::where('user_id', $user->id)->sum('amount');

        $incomesCount = Income::where('user_id', $user->id)->count();
        $incomesTotal = (float) Income::where('user_id', $user->id)->sum('amount');

        $lentLoans = Loan::where('user_id', $user->id)->where('type', 'lent')->get();
        $lentLoansCount = $lentLoans->count();
        $lentTotal = (float) $lentLoans->sum(fn($l) => $l->remaining_amount);

        $borrowedLoans = Loan::where('user_id', $user->id)->where('type', 'borrowed')->get();
        $borrowedLoansCount = $borrowedLoans->count();
        $borrowedTotal = (float) $borrowedLoans->sum(fn($l) => $l->remaining_amount);

        $savingsGoalsCount = SavingsGoal::where('user_id', $user->id)->count();
        $savingsTargetTotal = (float) SavingsGoal::where('user_id', $user->id)->sum('target_amount');
        $savingsCurrentTotal = (float) SavingsGoal::where('user_id', $user->id)->sum('current_amount');

        $budgetsCount = Budget::where('user_id', $user->id)->count();
        $customCategoriesCount = Category::where('user_id', $user->id)->count();

        // Latest 5 expenses
        $recentExpenses = Expense::with('category')
            ->where('user_id', $user->id)
            ->latest('date')
            ->latest('id')
            ->take(5)
            ->get()
            ->map(fn($e) => [
                'date' => $e->date->format('M d, Y'),
                'category' => $e->category ? $e->category->display_name : 'Uncategorized',
                'amount' => number_format($e->amount, 2),
                'note' => $e->note ?: '-',
            ]);

        // Latest 5 incomes
        $recentIncomes = Income::with('category')
            ->where('user_id', $user->id)
            ->latest('date')
            ->latest('id')
            ->take(5)
            ->get()
            ->map(fn($i) => [
                'date' => $i->date->format('M d, Y'),
                'category' => $i->category ? $i->category->display_name : ($i->source ?: 'Income'),
                'amount' => number_format($i->amount, 2),
                'note' => $i->note ?: '-',
            ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'initials' => $user->initials,
            'avatar_url' => $user->avatar_url,
            'is_admin' => $user->isAdmin(),
            'joined_date' => $user->created_at->format('M d, Y (h:i A)'),
            'financial' => [
                'expenses_count' => $expensesCount,
                'expenses_total' => number_format($expensesTotal, 2),
                'incomes_count' => $incomesCount,
                'incomes_total' => number_format($incomesTotal, 2),
                'balance' => number_format($incomesTotal - $expensesTotal, 2),
                'lent_count' => $lentLoansCount,
                'lent_total' => number_format($lentTotal, 2),
                'borrowed_count' => $borrowedLoansCount,
                'borrowed_total' => number_format($borrowedTotal, 2),
                'savings_goals_count' => $savingsGoalsCount,
                'savings_target' => number_format($savingsTargetTotal, 2),
                'savings_current' => number_format($savingsCurrentTotal, 2),
                'budgets_count' => $budgetsCount,
                'custom_categories_count' => $customCategoriesCount,
            ],
            'recent_expenses' => $recentExpenses,
            'recent_incomes' => $recentIncomes,
        ]);
    }

    /**
     * Admin resetting a user's password
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->back()->with('success', "Password for user '{$user->name}' ({$user->email}) has been successfully updated!");
    }

    /**
     * Toggle admin role for a user
     */
    public function toggleRole(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'You cannot modify your own administrator role.');
        }

        if ($user->isAdmin() && User::where('is_admin', true)->count() <= 1) {
            return redirect()->back()->with('error', 'Cannot demote the only remaining administrator in the system.');
        }

        $newRole = !$user->is_admin;
        $user->update(['is_admin' => $newRole]);

        $statusText = $newRole ? 'granted Administrator privileges' : 'demoted to Regular User';
        return redirect()->back()->with('success', "User '{$user->name}' has been {$statusText}.");
    }

    /**
     * Create a new user from the admin dashboard
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $newUser = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return redirect()->back()->with('success', "New user '{$newUser->name}' created successfully!");
    }

    /**
     * Safely cascade delete a user and all their financial records
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Security Protection: You cannot delete your own administrator account.');
        }

        if ($user->isAdmin() && User::where('is_admin', true)->count() <= 1) {
            return redirect()->back()->with('error', 'Cannot delete the only remaining administrator in the system.');
        }

        $userName = $user->name;
        $userEmail = $user->email;

        DB::transaction(function () use ($user) {
            // 1. Delete loan payments and loans
            LoanPayment::where('user_id', $user->id)->delete();
            Loan::where('user_id', $user->id)->delete();

            // 2. Delete recurring expenses
            RecurringExpense::where('user_id', $user->id)->delete();

            // 3. Delete budget categories and budgets
            BudgetCategory::whereHas('budget', fn($q) => $q->where('user_id', $user->id))->delete();
            Budget::where('user_id', $user->id)->delete();

            // 4. Delete savings transactions and goals
            SavingsTransaction::where('user_id', $user->id)->delete();
            SavingsGoal::where('user_id', $user->id)->delete();

            // 5. Delete expenses and receipts
            $expenses = Expense::where('user_id', $user->id)->get();
            foreach ($expenses as $exp) {
                if ($exp->receipt_path && Storage::disk('public')->exists($exp->receipt_path)) {
                    Storage::disk('public')->delete($exp->receipt_path);
                }
            }
            Expense::where('user_id', $user->id)->forceDelete();

            // 6. Delete incomes
            Income::where('user_id', $user->id)->forceDelete();

            // 7. Delete custom user categories
            Category::where('user_id', $user->id)->delete();

            // 8. Delete user avatar file if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // 9. Delete user record
            $user->delete();
        });

        return redirect()->back()->with('success', "User '{$userName}' ({$userEmail}) and all their associated records were permanently deleted.");
    }
}
