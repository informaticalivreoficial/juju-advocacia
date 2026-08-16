<?php

namespace Database\Factories;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Models\Client;
use App\Models\Process;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'deadline_id' => null,
            'client_id' => Client::factory(),
            'process_id' => Process::factory(),
            'responsible_user_id' => User::factory(),
            'title' => fake()->randomElement([
                'Redigir petição inicial',
                'Revisar contrato do cliente',
                'Agendar audiência',
                'Preparar recurso de apelação',
                'Atualizar planilha de honorários',
                'Ligar para o cliente',
                'Protocolar documentos',
            ]),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(TaskStatusEnum::cases())->value,
            'priority' => fake()->randomElement(TaskPriorityEnum::cases())->value,
            'due_date' => fake()->dateTimeBetween('-5 days', '+30 days')->format('Y-m-d'),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatusEnum::Completed->value,
            'completed_at' => fake()->dateTimeBetween('-15 days', 'now'),
        ]);
    }
}
