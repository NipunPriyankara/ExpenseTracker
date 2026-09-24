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
use App\Models\UserLoginLog;
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
        // System-wide aggregate metrics
        $totalUsers = User::count();
        $totalAdmins = User::where('is_admin', true)->count();
        $totalExpensesCount = Expense::count();
        $totalExpensesAmount = (float) Expense::sum('amount');
        $totalIncomesCount = Income::count();
        $totalIncomesAmount = (float) Income::sum('amount');
        $totalActiveLoans = Loan::where('status', 'active')->count();

        // Users Query - Eager load login logs count and latest login log
        // User financial details (incomes/expenses) are not exposed per-user to preserve privacy
        $query = User::withCount(['loginLogs'])
            ->with(['latestLoginLog']);

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
        } elseif ($sort === 'most_logins') {
            $query->orderByDesc('login_logs_count');
        } elseif ($sort === 'recent_login') {
            $query->orderByRaw('last_login_at IS NULL, last_login_at DESC');
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
     * Get user details and login activity history for a specific user (AJAX endpoint)
     * Note: Personal financial transactions and amounts are excluded to protect user privacy.
     */
    public function show(User $user)
    {
        $loginLogs = $user->loginLogs()
            ->latest('login_at')
            ->take(20)
            ->get();

        $totalLogins = $user->loginLogs()->count();
        $latestLogin = $loginLogs->first();

        // If user has no login logs recorded yet (e.g. registered before migration), synthesize initial log from registration
        if (!$latestLogin) {
            $latestLogin = $user->loginLogs()->create([
                'ip_address' => $user->last_login_ip ?: '127.0.0.1',
                'user_agent' => 'Initial Account Registration',
                'device' => 'Desktop',
                'browser' => 'System',
                'platform' => 'System',
                'login_at' => $user->created_at,
                'status' => 'success',
            ]);
            $totalLogins = 1;
            $loginLogs = collect([$latestLogin]);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'initials' => $user->initials,
            'avatar_url' => $user->avatar_url,
            'is_admin' => $user->isAdmin(),
            'joined_date' => $user->created_at->format('M d, Y (h:i A)'),
            'joined_diff' => $user->created_at->diffForHumans(),
            'login_stats' => [
                'total_logins' => $totalLogins,
                'last_login_formatted' => $latestLogin->login_at->format('M d, Y (h:i A)'),
                'last_login_diff' => $latestLogin->login_at->diffForHumans(),
                'last_ip' => $latestLogin->ip_address ?: '127.0.0.1',
                'last_device' => $latestLogin->device ?: 'Desktop',
                'last_platform' => $latestLogin->platform ?: 'OS',
                'last_browser' => $latestLogin->browser ?: 'Browser',
                'is_active_now' => $latestLogin->login_at->gt(now()->subMinutes(30)),
            ],
            'login_logs' => $loginLogs->map(fn($log) => [
                'id' => $log->id,
                'date_formatted' => $log->login_at->format('M d, Y'),
                'time_formatted' => $log->login_at->format('h:i:s A'),
                'diff_for_humans' => $log->login_at->diffForHumans(),
                'ip_address' => $log->ip_address ?: '127.0.0.1',
                'device' => $log->device ?: 'Desktop',
                'browser' => $log->browser ?: 'Browser',
                'platform' => $log->platform ?: 'OS',
                'status' => $log->status ?: 'success',
            ]),
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
