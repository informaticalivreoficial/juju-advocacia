<?php

namespace Database\Factories;

use App\Enums\ProcessAreaEnum;
use App\Enums\ProcessPriorityEnum;
use App\Enums\ProcessStatusEnum;
use App\Models\Client;
use App\Models\Process;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Process>
 */
class ProcessFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'client_id' => Client::factory(),
            'responsible_user_id' => User::factory(),
            'process_number' => fake()->unique()->numerify('##########-##.202#.#.##.####'),
            'title' => fake()->sentence(4),
            'area' => fake()->randomElement(ProcessAreaEnum::cases())->value,
            'action_type' => fake()->randomElement([
                'Indenização', 'Cobrança', 'Reclamação trabalhista', 'Guarda', 'Pensão alimentícia',
                'Revisional de contrato', 'Improbidade administrativa', 'Rescisão contratual',
            ]),
            'court' => fake()->randomElement(['TJSP', 'TRT-15', 'TRE', 'JFSP']),
            'district' => fake()->randomElement(['1ª Vara Cível', '2ª Vara Cível', 'Vara do Trabalho', 'Vara de Família']),
            'court_division' => null,
            'instance' => fake()->randomElement(['first', 'second']),
            'plaintiff' => fake()->name(),
            'defendant' => fake()->company(),
            'case_value' => fake()->randomFloat(2, 1000, 500000),
            'distribution_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement(ProcessStatusEnum::cases())->value,
            'priority' => fake()->randomElement(ProcessPriorityEnum::cases())->value,
            'confidentiality' => fake()->boolean(10),
            'description' => fake()->optional()->paragraph(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProcessStatusEnum::Active->value,
        ]);
    }
}
