<?php

namespace App\Models;

use App\Enums\FinancialType;
use App\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialCategory extends Model
{
    use BelongsToUser, HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'color',
        'icon',
        'active',
    ];

    protected $casts = [
        'type' => FinancialType::class,
        'active' => 'boolean',
    ];

    public function expenses(): HasMany
    {
        return $this->hasMany(FinancialExpense::class, 'category_id');
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(FinancialIncome::class, 'category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'category_id');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(FinancialBudget::class, 'category_id');
    }
}
