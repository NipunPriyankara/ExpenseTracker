<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses with search and filters.
     * Groups itemized / split bills into 1 master line with item breakdown.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $baseQuery = Expense::where('user_id', $userId);

        // Search in notes, payment methods, or transport details
        if ($search = $request->input('search')) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('note', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%")
                  ->orWhere('transport_mode', 'like', "%{$search}%")
                  ->orWhere('vehicle_type', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($catQuery) use ($search) {
                      $catQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Category
        if ($categoryId = $request->input('category_id')) {
            $baseQuery->where('category_id', $categoryId);
        }

        // Filter by Payment Method
        if ($paymentMethod = $request->input('payment_method')) {
            $baseQuery->where('payment_method', $paymentMethod);
        }

        // Filter by Transport Mode
        if ($transportMode = $request->input('transport_mode')) {
            $baseQuery->where('transport_mode', $transportMode);
        }

        // Filter by Date Range
        if ($startDate = $request->input('start_date')) {
            $baseQuery->whereDate('date', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $baseQuery->whereDate('date', '<=', $endDate);
        }

        // Summary metrics for current filtered query (all matching individual expenses)
        $totalFilteredAmount = (clone $baseQuery)->sum('amount');

        // Group by split_group_id so multiple items entered in 1 bill appear as 1 line
        $subQuery = (clone $baseQuery)
            ->selectRaw('MIN(id) as id')
            ->groupBy(DB::raw("COALESCE(split_group_id, CONCAT('single_', id))"));

        $query = Expense::whereIn('id', $subQuery)
            ->with(['category.parent', 'splitGroupItems.category.parent']);

        // Sorting
        $sort = $request->input('sort', 'newest');
        if ($sort === 'oldest') {
            $query->orderBy('date', 'asc')->orderBy('id', 'asc');
        } elseif ($sort === 'amount_high') {
            $query->orderBy('amount', 'desc');
        } elseif ($sort === 'amount_low') {
            $query->orderBy('amount', 'asc');
        } else {
            $query->orderBy('date', 'desc')->orderBy('id', 'desc');
        }

        $expenses = $query->paginate(10)->withQueryString();

        // All user-accessible categories with parent hierarchy
        $categories = Category::forUser($userId)
            ->where('type', 'expense')
            ->with('parent')
            ->orderBy('name')
            ->get();

        return view('expenses.index', compact('expenses', 'categories', 'totalFilteredAmount'));
    }

    /**
     * Store a newly created expense (supports single or split/itemized entries).
     */
    public function store(Request $request)
    {
        $userId = Auth::id();

        // Handle Split / Itemized Bill submission
        if ($request->boolean('is_split')) {
            $validated = $request->validate([
                'date' => ['required', 'date'],
                'payment_method' => ['required', 'string', 'in:Cash,Bank,Debit Card,Credit Card,Online Payment,Other'],
                'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
                'items.*.category_id' => ['required', 'exists:categories,id'],
                'items.*.note' => ['nullable', 'string', 'max:1000'],
            ]);

            $receiptPath = null;
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')->store('receipts', 'public');
            }

            $createdCount = 0;
            $totalAmount = 0;
            $splitGroupId = 'split_' . (string) Str::uuid();

            DB::transaction(function () use ($userId, $validated, $receiptPath, $splitGroupId, &$createdCount, &$totalAmount) {
                foreach ($validated['items'] as $item) {
                    // Security check: verify user can access this category
                    $category = Category::forUser($userId)->findOrFail($item['category_id']);

                    Expense::create([
                        'user_id' => $userId,
                        'category_id' => $category->id,
                        'split_group_id' => $splitGroupId,
                        'amount' => $item['amount'],
                        'date' => $validated['date'],
                        'payment_method' => $validated['payment_method'],
                        'note' => !empty($item['note']) ? trim($item['note']) : null,
                        'receipt_path' => $receiptPath,
                    ]);

                    $createdCount++;
                    $totalAmount += (float)$item['amount'];
                }
            });

            return redirect()->back()->with('success', "Successfully recorded itemized bill of {$createdCount} items totaling Rs. " . number_format($totalAmount, 2) . "!");
        }

        // Standard Single Expense submission
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'category_id' => ['required', 'exists:categories,id'],
            'date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:Cash,Bank,Debit Card,Credit Card,Online Payment,Other'],
            'transport_mode' => ['nullable', 'string', 'in:Bus,PickMe,Uber,Train,Other'],
            'vehicle_type' => ['nullable', 'string', 'in:3-Wheel,Bike,Car,Van,Other'],
            'note' => ['nullable', 'string', 'max:1000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
        ]);

        // Security check: verify user can use this category
        $category = Category::forUser($userId)->findOrFail($validated['category_id']);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $transportMode = $validated['transport_mode'] ?? null;
        $vehicleType = ($transportMode && in_array($transportMode, ['PickMe', 'Uber', 'Other']))
            ? ($validated['vehicle_type'] ?? null)
            : null;

        Expense::create([
            'user_id' => $userId,
            'category_id' => $category->id,
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'payment_method' => $validated['payment_method'],
            'transport_mode' => $transportMode,
            'vehicle_type' => $vehicleType,
            'note' => $validated['note'] ? trim($validated['note']) : null,
            'receipt_path' => $receiptPath,
        ]);

        return redirect()->back()->with('success', 'Expense of Rs. ' . number_format($validated['amount'], 2) . ' recorded successfully!');
    }

    /**
     * Show the form for editing an expense.
     */
    public function edit(Expense $expense)
    {
        // Security check: user ownership
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $categories = Category::forUser(Auth::id())->where('type', 'expense')->with('parent')->orderBy('name')->get();

        return view('expenses.edit', compact('expense', 'categories'));
    }

    /**
     * Update the specified expense.
     */
    public function update(Request $request, Expense $expense)
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $userId = Auth::id();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'category_id' => ['required', 'exists:categories,id'],
            'date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'in:Cash,Bank,Debit Card,Credit Card,Online Payment,Other'],
            'transport_mode' => ['nullable', 'string', 'in:Bus,PickMe,Uber,Train,Other'],
            'vehicle_type' => ['nullable', 'string', 'in:3-Wheel,Bike,Car,Van,Other'],
            'note' => ['nullable', 'string', 'max:1000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
            'remove_receipt' => ['nullable', 'boolean'],
        ]);

        $category = Category::forUser($userId)->findOrFail($validated['category_id']);

        $receiptPath = $expense->receipt_path;

        // Check if user requested to remove existing receipt
        if ($request->boolean('remove_receipt') && $receiptPath) {
            Storage::disk('public')->delete($receiptPath);
            $receiptPath = null;
        }

        // Check if user uploaded a new receipt
        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $receiptPath = $request->file('receipt')->store('receipts', 'public');
        }

        $transportMode = $validated['transport_mode'] ?? null;
        $vehicleType = ($transportMode && in_array($transportMode, ['PickMe', 'Uber', 'Other']))
            ? ($validated['vehicle_type'] ?? null)
            : null;

        $expense->update([
            'category_id' => $category->id,
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'payment_method' => $validated['payment_method'],
            'transport_mode' => $transportMode,
            'vehicle_type' => $vehicleType,
            'note' => $validated['note'] ? trim($validated['note']) : null,
            'receipt_path' => $receiptPath,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully!');
    }

    /**
     * Remove the specified expense (soft delete).
     */
    public function destroy(Expense $expense)
    {
        if ($expense->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($expense->split_group_id) {
            Expense::where('user_id', Auth::id())
                ->where('split_group_id', $expense->split_group_id)
                ->delete();
            return redirect()->back()->with('success', 'Split / Itemized bill and all its items deleted successfully.');
        }

        $expense->delete();

        return redirect()->back()->with('success', 'Expense deleted successfully.');
    }
}
