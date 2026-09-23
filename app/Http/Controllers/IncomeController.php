<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IncomeController extends Controller
{
    /**
     * Display a listing of incomes with search and filters.
     * Groups itemized / split incomes into 1 master line with item breakdown.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();

        $baseQuery = Income::where('user_id', $userId);

        // Search in note, source, or category name
        if ($search = $request->input('search')) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('source', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($catQuery) use ($search) {
                      $catQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by Category
        if ($categoryId = $request->input('category_id')) {
            $baseQuery->where('category_id', $categoryId);
        }

        // Filter by Source (legacy support)
        if ($source = $request->input('source')) {
            $baseQuery->where('source', $source);
        }

        // Filter by Date Range
        if ($startDate = $request->input('start_date')) {
            $baseQuery->whereDate('date', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $baseQuery->whereDate('date', '<=', $endDate);
        }

        // Total filtered amount (all matching individual income rows)
        $totalFilteredAmount = (clone $baseQuery)->sum('amount');

        // Group by split_group_id so multiple items entered in 1 transaction appear as 1 line
        $subQuery = (clone $baseQuery)
            ->selectRaw('MIN(id) as id')
            ->groupBy(DB::raw("COALESCE(split_group_id, CONCAT('single_', id))"));

        $query = Income::whereIn('id', $subQuery)
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

        $incomes = $query->paginate(10)->withQueryString();

        // Income categories with parent hierarchy
        $categories = Category::forUser($userId)
            ->where('type', 'income')
            ->with('parent')
            ->orderBy('name')
            ->get();

        $sources = ['Salary', 'Freelance', 'Business', 'Bonus', 'Investments', 'Other'];

        return view('incomes.index', compact('incomes', 'categories', 'sources', 'totalFilteredAmount'));
    }

    /**
     * Store a newly created income (supports single or itemized/multiple entries).
     */
    public function store(Request $request)
    {
        $userId = Auth::id();

        // Handle Split / Multiple Itemized Income submission
        if ($request->boolean('is_split')) {
            $validated = $request->validate([
                'date' => ['required', 'date'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
                'items.*.category_id' => ['required', 'exists:categories,id'],
                'items.*.note' => ['nullable', 'string', 'max:1000'],
            ]);

            $createdCount = 0;
            $totalAmount = 0;
            $splitGroupId = 'split_inc_' . (string) Str::uuid();

            DB::transaction(function () use ($userId, $validated, $splitGroupId, &$createdCount, &$totalAmount) {
                foreach ($validated['items'] as $item) {
                    $category = Category::forUser($userId)->findOrFail($item['category_id']);

                    Income::create([
                        'user_id' => $userId,
                        'category_id' => $category->id,
                        'split_group_id' => $splitGroupId,
                        'source' => $category->name,
                        'amount' => $item['amount'],
                        'date' => $validated['date'],
                        'note' => !empty($item['note']) ? trim($item['note']) : null,
                    ]);

                    $createdCount++;
                    $totalAmount += (float)$item['amount'];
                }
            });

            return redirect()->back()->with('success', "Successfully recorded itemized income of {$createdCount} items totaling Rs. " . number_format($totalAmount, 2) . "!");
        }

        // Standard Single Income submission
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'category_id' => ['required', 'exists:categories,id'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = Category::forUser($userId)->findOrFail($validated['category_id']);

        Income::create([
            'user_id' => $userId,
            'category_id' => $category->id,
            'source' => $category->name,
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'note' => !empty($validated['note']) ? trim($validated['note']) : null,
        ]);

        return redirect()->back()->with('success', 'Income of Rs. ' . number_format($validated['amount'], 2) . ' recorded successfully under "' . $category->display_name . '"!');
    }

    public function edit(Income $income)
    {
        if ($income->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $userId = Auth::id();
        $income->load(['category.parent', 'splitGroupItems.category.parent']);

        $categories = Category::forUser($userId)
            ->where('type', 'income')
            ->with('parent')
            ->orderBy('name')
            ->get();

        $sources = ['Salary', 'Freelance', 'Business', 'Bonus', 'Investments', 'Other'];

        return view('incomes.edit', compact('income', 'categories', 'sources'));
    }

    public function update(Request $request, Income $income)
    {
        if ($income->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $userId = Auth::id();
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'category_id' => ['required', 'exists:categories,id'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = Category::forUser($userId)->findOrFail($validated['category_id']);

        $income->update([
            'category_id' => $category->id,
            'source' => $category->name,
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'note' => !empty($validated['note']) ? trim($validated['note']) : null,
        ]);

        return redirect()->route('incomes.index')->with('success', 'Income record updated successfully!');
    }

    public function destroy(Request $request, Income $income)
    {
        if ($income->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // If part of a split group and user opted to delete all items in group
        if ($income->isSplit() && $request->boolean('delete_all_split')) {
            Income::where('split_group_id', $income->split_group_id)
                ->where('user_id', Auth::id())
                ->delete();

            return redirect()->back()->with('success', 'Entire itemized income group deleted successfully.');
        }

        $income->delete();

        return redirect()->back()->with('success', 'Income record deleted successfully.');
    }
}
