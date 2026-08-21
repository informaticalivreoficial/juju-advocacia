<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialIncome extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'description',
        'receive_day',
        'amount',
        'notes',
        'active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class);
    }
}
