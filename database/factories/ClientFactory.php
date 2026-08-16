<?php

namespace Database\Factories;

use App\Enums\ClientTypeEnum;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(ClientTypeEnum::cases());

        return [
            'uuid' => (string) Str::uuid(),
            'type' => $type->value,
            'name' => $type === ClientTypeEnum::Individual ? fake()->name() : null,
            'document' => $type === ClientTypeEnum::Individual ? $this->fakeCpf() : $this->fakeCnpj(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('(##) ####-####'),
            'mobile' => fake()->numerify('(##) #####-####'),
            'birth_date' => fake()->dateTimeBetween('-60 years', '-20 years')->format('Y-m-d'),
            'marital_status' => fake()->randomElement(['solteiro', 'casado', 'divorciado', 'viuvo', 'uniao_estavel']),
            'profession' => fake()->jobTitle(),
            'company_name' => $type === ClientTypeEnum::Company ? fake()->company() : null,
            'trade_name' => null,
            'state_registration' => $type === ClientTypeEnum::Company ? fake()->numerify('###.###.###') : null,
            'zip_code' => fake()->numerify('########'),
            'address' => fake()->streetAddress(),
            'number' => fake()->buildingNumber(),
            'complement' => null,
            'neighborhood' => fake()->word(),
            'city' => fake()->city(),
            'state' => fake()->randomElement(['SP', 'RJ', 'MG', 'PR', 'SC', 'RS', 'BA']),
            'notes' => null,
            'is_active' => true,
        ];
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ClientTypeEnum::Individual->value,
            'name' => fake()->name(),
            'company_name' => null,
            'document' => $this->fakeCpf(),
        ]);
    }

    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ClientTypeEnum::Company->value,
            'name' => null,
            'company_name' => fake()->company(),
            'document' => $this->fakeCnpj(),
        ]);
    }

    private function fakeCpf(): string
    {
        $base = array_map(fn () => random_int(0, 9), range(1, 9));

        return implode('', $base).$this->cpfDigits($base, 9).$this->cpfDigits($base, 10);
    }

    private function cpfDigits(array $base, int $t): int
    {
        $sum = 0;

        foreach ($base as $index => $digit) {
            $sum += $digit * (($t + 1) - $index);
        }

        return ((10 * $sum) % 11) % 10;
    }

    private function fakeCnpj(): string
    {
        $base = array_map(fn () => random_int(0, 9), range(1, 12));
        $first = $this->cnpjDigit($base, [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        $second = $this->cnpjDigit([...$base, $first], [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        return implode('', $base).$first.$second;
    }

    private function cnpjDigit(array $digits, array $weights): int
    {
        $sum = 0;

        foreach ($weights as $index => $weight) {
            $sum += $digits[$index] * $weight;
        }

        $rest = $sum % 11;

        return $rest < 2 ? 0 : 11 - $rest;
    }
}
