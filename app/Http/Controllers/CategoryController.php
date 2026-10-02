<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $selectedType = $request->input('type', 'all');

        $query = Category::forUser($userId)
            ->with(['parent', 'subcategories']);

        if (in_array($selectedType, ['expense', 'income'])) {
            $query->where('type', $selectedType);
        }

        $categories = $query->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        $parentCategories = Category::forUser($userId)
            ->whereNull('parent_id')
            ->orderBy('name', 'asc')
            ->get();

        $expenseCount = Category::forUser($userId)->where('type', 'expense')->count();
        $incomeCount = Category::forUser($userId)->where('type', 'income')->count();

        return view('categories.index', compact('categories', 'parentCategories', 'selectedType', 'expenseCount', 'incomeCount'));
    }

    public function store(Request $request)
    {
        if ($request->has('parent_id') && empty($request->input('parent_id'))) {
            $request->merge(['parent_id' => null]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'type' => ['nullable', 'in:expense,income'],
        ]);

        $parent = !empty($validated['parent_id']) ? Category::forUser(Auth::id())->find($validated['parent_id']) : null;
        $type = $validated['type'] ?? ($parent ? $parent->type : 'expense');
        $color = $validated['color'] ?: ($parent ? $parent->color : '#4f46e5');
        $icon = $validated['icon'] ?: ($parent ? $parent->icon : 'bi-tag');

        $category = Category::create([
            'user_id' => Auth::id(),
            'parent_id' => $parent ? $parent->id : null,
            'name' => trim($validated['name']),
            'icon' => $icon,
            'color' => $color,
            'type' => $type,
            'is_default' => false,
        ]);

        return redirect()->back()->with('success', 'Category "' . $category->name . '" created successfully!');
    }

    /**
     * AJAX quick store for creating category/subcategory from modals
     */
    public function quickStore(Request $request)
    {
        if ($request->has('parent_id') && empty($request->input('parent_id'))) {
            $request->merge(['parent_id' => null]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'type' => ['nullable', 'in:expense,income'],
        ]);

        $parent = !empty($validated['parent_id']) ? Category::forUser(Auth::id())->find($validated['parent_id']) : null;
        $type = $validated['type'] ?? ($parent ? $parent->type : 'expense');
        $color = !empty($validated['color']) ? $validated['color'] : ($parent ? $parent->color : ($type === 'income' ? '#10b981' : '#4f46e5'));
        $icon = !empty($validated['icon']) ? $validated['icon'] : ($parent ? $parent->icon : ($type === 'income' ? 'bi-cash-coin' : 'bi-tag'));

        $category = Category::create([
            'user_id' => Auth::id(),
            'parent_id' => $parent ? $parent->id : null,
            'name' => trim($validated['name']),
            'icon' => $icon,
            'color' => $color,
            'type' => $type,
            'is_default' => false,
        ]);

        return response()->json([
            'success' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'display_name' => $category->display_name,
                'parent_id' => $category->parent_id,
                'type' => $category->type,
                'icon' => $category->icon,
                'color' => $category->color,
            ],
            'message' => 'Category "' . $category->display_name . '" created successfully!',
        ]);
    }

    /**
     * Load default set of categories for current logged in user.
     */
    public function loadDefaults()
    {
        Category::seedDefaultCategoriesForUser(Auth::id());
        return redirect()->back()->with('success', 'Default categories loaded successfully!');
    }

    public function destroy(Category $category)
    {
        // Security check: only owner can delete their categories
        if ($category->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You cannot delete categories belonging to other users.');
        }

        // Check if in use
        if ($category->expenses()->count() > 0 || $category->incomes()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete this category because it is already used in recorded transactions.');
        }

        if ($category->subcategories()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete this category because it has subcategories attached.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
