<?php

namespace Database\Factories;

use App\Models\FinancialCategory;
use App\Models\FinancialIncome;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialIncome>
 */
class FinancialIncomeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => FinancialCategory::factory()->income(),
            'description' => fake()->randomElement([
                'Honorários advocatícios',
                'Consultoria jurídica',
                'Mensalidade de clientes',
                'Correção monetária',
            ]),
            'receive_day' => fake()->numberBetween(1, 28),
            'amount' => fake()->randomFloat(2, 100, 10000),
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
