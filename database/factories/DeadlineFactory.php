<?php

namespace Database\Factories;

use App\Enums\DeadlinePriorityEnum;
use App\Enums\DeadlineStatusEnum;
use App\Models\Deadline;
use App\Models\Process;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Deadline>
 */
class DeadlineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'process_id' => Process::factory(),
            'responsible_user_id' => User::factory(),
            'title' => fake()->randomElement([
                'Contestar prazo',
                'Protocolo de recurso',
                'Juntada de procuração',
                'Pagar custas processuais',
                'Manifestação sobre laudo',
                'Aguardar intimação da sentença',
            ]),
            'description' => fake()->optional()->paragraph(),
            'start_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'due_date' => fake()->dateTimeBetween('-15 days', '+30 days')->format('Y-m-d'),
            'completed_at' => null,
            'status' => fake()->randomElement([
                DeadlineStatusEnum::Pending,
                DeadlineStatusEnum::InProgress,
                DeadlineStatusEnum::Completed,
            ])->value,
            'priority' => fake()->randomElement(DeadlinePriorityEnum::cases())->value,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeadlineStatusEnum::Completed->value,
            'completed_at' => fake()->dateTimeBetween('-15 days', 'now'),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DeadlineStatusEnum::Pending->value,
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day')->format('Y-m-d'),
        ]);
    }
}
