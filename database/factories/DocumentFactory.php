<?php

namespace Database\Factories;

use App\Enums\DocumentCategoryEnum;
use App\Models\Client;
use App\Models\Document;
use App\Models\Process;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'process_id' => Process::factory(),
            'client_id' => Client::factory(),
            'uploaded_by' => User::factory(),
            'title' => fake()->randomElement([
                'Contrato de honorários',
                'Procuração ad judicia',
                'Petição inicial',
                'Sentença',
                'Certidão de distribuição',
                'Relatório mensal',
                'Acórdão',
            ]),
            'description' => fake()->optional()->sentence(),
            'category' => fake()->randomElement(DocumentCategoryEnum::cases())->value,
            'file_path' => 'documents/'.Str::uuid().'.pdf',
            'file_name' => Str::slug(fake()->words(3, true)).'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(50_000, 2_000_000),
        ];
    }
}
