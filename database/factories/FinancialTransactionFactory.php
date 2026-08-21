<?php

namespace Database\Factories;

use App\Enums\FinancialPaymentMethod;
use App\Enums\FinancialStatus;
use App\Enums\FinancialType;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialTransaction>
 */
class FinancialTransactionFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement([FinancialType::Income->value, FinancialType::Expense->value]);

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'category_id' => FinancialCategory::factory(),
            'expense_id' => null,
            'income_id' => null,
            'year' => now()->year,
            'month' => now()->month,
            'amount' => fake()->randomFloat(2, 50, 5000),
            'status' => FinancialStatus::Pending->value,
            'paid_at' => null,
            'received_at' => null,
            'notes' => fake()->optional()->sentence(),
            'description' => fake()->sentence(3),
            'payment_method' => fake()->randomElement(FinancialPaymentMethod::cases())->value,
            'due_date' => fake()->date('Y-m-d', now()->addDays(30)->format('Y-m-d')),
            'attachment_path' => null,
            'due_notified_at' => null,
        ];
    }

    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => FinancialType::Expense->value,
            'category_id' => FinancialCategory::factory()->expense(),
        ]);
    }

    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => FinancialType::Income->value,
            'category_id' => FinancialCategory::factory()->income(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FinancialStatus::Pending->value,
            'paid_at' => null,
            'received_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FinancialStatus::Paid->value,
            'paid_at' => now(),
        ]);
    }

    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FinancialStatus::Received->value,
            'received_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => FinancialStatus::Cancelled->value,
        ]);
    }

    public function forMonth(int $year, int $month): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $year,
            'month' => $month,
        ]);
    }
}
