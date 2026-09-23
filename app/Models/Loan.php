<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type', // 'borrowed' (I took loan), 'lent' (I gave loan)
        'person_name',
        'name',
        'total_amount',
        'monthly_installment',
        'interest_rate',
        'start_date',
        'end_date',
        'paid_amount',
        'status', // 'active', 'settled'
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class)->orderBy('payment_date', 'desc')->orderBy('id', 'desc');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, round((float)$this->total_amount - (float)$this->paid_amount, 2));
    }

    public function getPaidPercentageAttribute(): float
    {
        if ((float)$this->total_amount <= 0) {
            return 100.0;
        }
        return min(100.0, round(((float)$this->paid_amount / (float)$this->total_amount) * 100, 1));
    }

    public function isBorrowed(): bool
    {
        return $this->type === 'borrowed';
    }

    public function isLent(): bool
    {
        return $this->type === 'lent';
    }

    public function isSettled(): bool
    {
        return $this->status === 'settled' || $this->remaining_amount <= 0.00;
    }
}
