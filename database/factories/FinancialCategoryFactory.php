<?php

namespace Database\Factories;

use App\Enums\FinancialType;
use App\Models\FinancialCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialCategory>
 */
class FinancialCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement([
                'Honorários',
                'Aluguel',
                'Material de escritório',
                'Impostos',
                'Salários',
                'Internet',
                'Energia',
                'Água',
                'Marketing',
            ]),
            'type' => fake()->randomElement([FinancialType::Expense->value, FinancialType::Income->value]),
            'color' => fake()->randomElement(['#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f59e0b', '#10b981']),
            'icon' => 'tag',
            'active' => true,
        ];
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => FinancialType::Expense->value,
            'name' => fake()->randomElement(['Aluguel', 'Material de escritório', 'Impostos', 'Internet', 'Energia', 'Salários']),
        ]);
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => FinancialType::Income->value,
            'name' => fake()->randomElement(['Honorários', 'Consultoria', 'Aulões', 'Correção monetária']),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
