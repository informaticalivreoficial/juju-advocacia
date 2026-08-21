<?php

namespace Database\Factories;

use App\Models\FinancialCategory;
use App\Models\FinancialExpense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialExpense>
 */
class FinancialExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => FinancialCategory::factory()->expense(),
            'description' => fake()->randomElement([
                'Aluguel do escritório',
                'Conta de luz',
                'Internet',
                'Material de escritório',
                'Impostos mensais',
                'Salário da equipe',
            ]),
            'due_day' => fake()->numberBetween(1, 28),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'notes' => fake()->optional()->sentence(),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
