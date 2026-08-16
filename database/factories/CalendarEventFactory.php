<?php

namespace Database\Factories;

use App\Enums\CalendarEventTypeEnum;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween(now()->startOfMonth(), now()->endOfMonth());

        return [
            'uuid' => (string) Str::uuid(),
            'process_id' => null,
            'client_id' => Client::factory(),
            'responsible_user_id' => User::factory(),
            'title' => fake()->randomElement([
                'Audiência de conciliação',
                'Reunião com cliente',
                'Audiência de instrução',
                'Videoconferência com sócios',
                'Entrega de documentos',
                'Consulta inicial',
                'Depoimento pessoal',
            ]),
            'description' => fake()->optional()->paragraph(),
            'type' => fake()->randomElement(CalendarEventTypeEnum::cases())->value,
            'start_datetime' => $start->format('Y-m-d H:i:s'),
            'end_datetime' => fake()->boolean(50)
                ? CarbonImmutable::instance($start)->addHour()->format('Y-m-d H:i:s')
                : null,
            'all_day' => fake()->boolean(15),
            'location' => fake()->optional()->randomElement(['Fórum Central', 'Sala de audiências 3', 'Escritório', 'Videoconferência']),
        ];
    }

    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'start_datetime' => "{$date} 10:00:00",
            'end_datetime' => "{$date} 11:00:00",
        ]);
    }
}
