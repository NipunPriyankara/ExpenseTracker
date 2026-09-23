<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'target_amount',
        'current_amount',
        'target_date',
        'icon',
        'color',
        'status', // 'active', 'completed'
        'notes',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class)->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, round((float)$this->target_amount - (float)$this->current_amount, 2));
    }

    public function getProgressPercentageAttribute(): float
    {
        if ((float)$this->target_amount <= 0) {
            return 100.0;
        }
        return min(100.0, round(((float)$this->current_amount / (float)$this->target_amount) * 100, 1));
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' || (float)$this->current_amount >= (float)$this->target_amount;
    }
}
