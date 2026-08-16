<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\Process;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => UserRoleEnum::Admin,
        ]);

        foreach (array_filter(UserRoleEnum::cases(), fn ($role) => $role !== UserRoleEnum::Admin) as $role) {
            User::factory()->create([
                'name' => $role->label(),
                'email' => strtolower($role->value).'@example.com',
                'password' => 'password',
                'role' => $role,
            ]);
        }

        $clients = Client::factory()->count(10)->create();

        $processes = Process::factory()
            ->count(15)
            ->create()
            ->each(function (Process $process) use ($clients) {
                $process->update([
                    'client_id' => $clients->random()->id,
                    'responsible_user_id' => User::inRandomOrder()->first()->id,
                ]);
            });

        $users = User::all();

        Deadline::factory()
            ->count(12)
            ->create()
            ->each(function (Deadline $deadline) use ($processes, $users) {
                $deadline->update([
                    'process_id' => $processes->random()->id,
                    'responsible_user_id' => $users->random()->id,
                ]);
            });

        Deadline::factory()
            ->count(3)
            ->completed()
            ->create()
            ->each(function (Deadline $deadline) use ($processes, $users) {
                $deadline->update([
                    'process_id' => $processes->random()->id,
                    'responsible_user_id' => $users->random()->id,
                ]);
            });

        $deadlines = Deadline::all();

        Task::factory()
            ->count(12)
            ->create()
            ->each(function (Task $task) use ($clients, $processes, $users, $deadlines) {
                $task->update([
                    'client_id' => $clients->random()->id,
                    'process_id' => $processes->random()->id,
                    'responsible_user_id' => $users->random()->id,
                    'deadline_id' => fake()->boolean(60) ? $deadlines->random()->id : null,
                ]);
            });

        CalendarEvent::factory()
            ->count(20)
            ->create()
            ->each(function (CalendarEvent $event) use ($clients, $processes, $users) {
                $event->update([
                    'client_id' => $clients->random()->id,
                    'process_id' => fake()->boolean(60) ? $processes->random()->id : null,
                    'responsible_user_id' => $users->random()->id,
                ]);
            });

        $titles = [
            'Contrato de honorários',
            'Procuração ad judicia',
            'Petição inicial',
            'Contestação',
            'Sentença de primeiro grau',
            'Certidão de distribuição',
            'Relatório mensal de atividades',
            'Acórdão do tribunal',
            'Planilha de honorários',
            'Documento de identificação do cliente',
            'Requerimento de justiça gratuita',
            'Memoriais finais',
            'Ata de audiência',
            'Alvará judicial',
            'Extrato de custas processuais',
        ];

        foreach ($titles as $index => $title) {
            $path = 'documents/seed-'.Str::uuid().'.pdf';
            Storage::put($path, 'Documento de demonstração: '.$title);

            Document::create([
                'process_id' => fake()->boolean(70) ? $processes->random()->id : null,
                'client_id' => $clients->random()->id,
                'uploaded_by' => $users->random()->id,
                'title' => $title,
                'description' => fake()->optional()->sentence(),
                'category' => fake()->randomElement([
                    'contract', 'petition', 'decision', 'certificate', 'power_of_attorney', 'report', 'other',
                ]),
                'file_path' => $path,
                'file_name' => Str::slug($title).'.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => Storage::size($path),
            ]);
        }
    }
}
