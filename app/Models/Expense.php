<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'split_group_id',
        'amount',
        'date',
        'payment_method',
        'transport_mode',
        'vehicle_type',
        'note',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function isSplit(): bool
    {
        return !empty($this->split_group_id);
    }

    public function splitGroupItems()
    {
        return $this->hasMany(Expense::class, 'split_group_id', 'split_group_id');
    }
}
