<?php

namespace App\Models;

use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialBudget extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'year',
        'month',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class);
    }
}
