<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
        'icon',
        'color',
        'type',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function subcategories(): HasMany
    {
        $userId = auth()->id();
        return $this->hasMany(Category::class, 'parent_id')
            ->where(function ($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhere(function ($q2) {
                          $q2->whereNull('user_id')->where('is_default', true);
                      });
                } else {
                    $q->whereNull('user_id')->where('is_default', true);
                }
            })
            ->orderBy('name');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function budgetCategories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class);
    }

    public function recurringExpenses(): HasMany
    {
        return $this->hasMany(RecurringExpense::class);
    }

    /**
     * Get display name with parent category if available (e.g. "Bills › Dialog TV").
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->parent_id && $this->relationLoaded('parent') && $this->parent) {
            return $this->parent->name . ' › ' . $this->name;
        } elseif ($this->parent_id) {
            $parent = Category::find($this->parent_id);
            return $parent ? $parent->name . ' › ' . $this->name : $this->name;
        }
        return $this->name;
    }

    /**
     * Scope to retrieve categories belonging strictly to a specific user.
     */
    public function scopeForUser($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $query->where('user_id', $userId);
    }

    /**
     * Seed a full set of default categories and subcategories specifically for a user.
     */
    public static function seedDefaultCategoriesForUser($userId): void
    {
        if (!$userId) return;

        $defaultExpenseCategories = [
            ['name' => 'Food',          'icon' => 'bi-cup-hot',      'color' => '#f97316'],
            ['name' => 'Transport',     'icon' => 'bi-bus-front',    'color' => '#0ea5e9'],
            ['name' => 'Fuel',          'icon' => 'bi-fuel-pump',    'color' => '#eab308'],
            ['name' => 'Bills',         'icon' => 'bi-receipt',      'color' => '#ef4444'],
            ['name' => 'Mobile',        'icon' => 'bi-phone',        'color' => '#8b5cf6'],
            ['name' => 'Internet',      'icon' => 'bi-wifi',         'color' => '#06b6d4'],
            ['name' => 'Shopping',      'icon' => 'bi-bag',          'color' => '#ec4899'],
            ['name' => 'Entertainment', 'icon' => 'bi-film',         'color' => '#a855f7'],
            ['name' => 'Education',     'icon' => 'bi-book',         'color' => '#3b82f6'],
            ['name' => 'Health',        'icon' => 'bi-heart-pulse',  'color' => '#10b981'],
            ['name' => 'Rent',          'icon' => 'bi-house-door',   'color' => '#6366f1'],
            ['name' => 'Loan',          'icon' => 'bi-bank',         'color' => '#64748b'],
            ['name' => 'Vehicle',       'icon' => 'bi-car-front',    'color' => '#14b8a6'],
            ['name' => 'Family',        'icon' => 'bi-people',       'color' => '#f43f5e'],
            ['name' => 'Other',         'icon' => 'bi-three-dots',   'color' => '#94a3b8'],
        ];

        foreach ($defaultExpenseCategories as $cat) {
            self::firstOrCreate(
                [
                    'user_id' => $userId,
                    'name' => $cat['name'],
                    'type' => 'expense',
                ],
                [
                    'icon' => $cat['icon'],
                    'color' => $cat['color'],
                    'is_default' => false,
                ]
            );
        }

        $defaultIncomeCategories = [
            [
                'name' => 'Salary & Wages',
                'icon' => 'bi-cash-stack',
                'color' => '#10b981',
                'subcategories' => [
                    ['name' => 'Basic Salary', 'icon' => 'bi-cash', 'color' => '#10b981'],
                    ['name' => 'Allowance', 'icon' => 'bi-award', 'color' => '#10b981'],
                    ['name' => 'Overtime (OT)', 'icon' => 'bi-clock-history', 'color' => '#10b981'],
                    ['name' => 'Bonus / Incentives', 'icon' => 'bi-stars', 'color' => '#10b981'],
                ],
            ],
            [
                'name' => 'Freelance & Services',
                'icon' => 'bi-laptop',
                'color' => '#06b6d4',
                'subcategories' => [
                    ['name' => 'Web Development', 'icon' => 'bi-code-slash', 'color' => '#06b6d4'],
                    ['name' => 'Consulting', 'icon' => 'bi-briefcase', 'color' => '#06b6d4'],
                    ['name' => 'Content & Design', 'icon' => 'bi-palette', 'color' => '#06b6d4'],
                ],
            ],
            [
                'name' => 'Business & Sales',
                'icon' => 'bi-shop',
                'color' => '#3b82f6',
                'subcategories' => [
                    ['name' => 'Product Sales', 'icon' => 'bi-box-seam', 'color' => '#3b82f6'],
                    ['name' => 'Service Fees', 'icon' => 'bi-receipt', 'color' => '#3b82f6'],
                    ['name' => 'Commissions', 'icon' => 'bi-percent', 'color' => '#3b82f6'],
                ],
            ],
            [
                'name' => 'Investments & Passive',
                'icon' => 'bi-graph-up-arrow',
                'color' => '#8b5cf6',
                'subcategories' => [
                    ['name' => 'Bank Interest', 'icon' => 'bi-bank', 'color' => '#8b5cf6'],
                    ['name' => 'Dividends', 'icon' => 'bi-piggy-bank', 'color' => '#8b5cf6'],
                    ['name' => 'Rental Income', 'icon' => 'bi-house-door', 'color' => '#8b5cf6'],
                    ['name' => 'Capital Gains', 'icon' => 'bi-arrow-up-right-circle', 'color' => '#8b5cf6'],
                ],
            ],
            [
                'name' => 'Gifts & Grants',
                'icon' => 'bi-gift',
                'color' => '#ec4899',
                'subcategories' => [],
            ],
            [
                'name' => 'Other Income',
                'icon' => 'bi-three-dots',
                'color' => '#64748b',
                'subcategories' => [],
            ],
        ];

        foreach ($defaultIncomeCategories as $catData) {
            $parent = self::firstOrCreate(
                [
                    'user_id' => $userId,
                    'name' => $catData['name'],
                    'type' => 'income',
                ],
                [
                    'icon' => $catData['icon'],
                    'color' => $catData['color'],
                    'parent_id' => null,
                    'is_default' => false,
                ]
            );

            foreach ($catData['subcategories'] as $subData) {
                self::firstOrCreate(
                    [
                        'user_id' => $userId,
                        'name' => $subData['name'],
                        'type' => 'income',
                    ],
                    [
                        'parent_id' => $parent->id,
                        'icon' => $subData['icon'],
                        'color' => $subData['color'],
                        'is_default' => false,
                    ]
                );
            }
        }
    }
}
