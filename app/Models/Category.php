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
        return $this->hasMany(Category::class, 'parent_id')->orderBy('name');
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
     * Scope to retrieve categories accessible to a specific user (their own + defaults).
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id')
              ->orWhere('is_default', true);
        });
    }
}
