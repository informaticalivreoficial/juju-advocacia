<?php

namespace App\Providers;

use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\Process;
use App\Models\Task;
use App\Models\User;
use App\Policies\CalendarEventPolicy;
use App\Policies\ClientPolicy;
use App\Policies\DeadlinePolicy;
use App\Policies\DocumentPolicy;
use App\Policies\ProcessPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Client::class => ClientPolicy::class,
        Process::class => ProcessPolicy::class,
        Deadline::class => DeadlinePolicy::class,
        Task::class => TaskPolicy::class,
        CalendarEvent::class => CalendarEventPolicy::class,
        Document::class => DocumentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Administradores possuem acesso total a qualquer habilidade.
        Gate::before(function ($user, $ability) {
            if ($user instanceof User && $user->isAdmin()) {
                return true;
            }
        });
    }
}
