<?php

namespace Database\Factories;

use App\Models\FinancialBudget;
use App\Models\FinancialCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialBudget>
 */
class FinancialBudgetFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => FinancialCategory::factory()->expense(),
            'year' => now()->year,
            'month' => now()->month,
            'amount' => fake()->randomFloat(2, 100, 10000),
        ];
    }
}
