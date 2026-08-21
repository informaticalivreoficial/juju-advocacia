<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\Deadline;
use App\Models\Document;
use App\Models\Process;
use App\Models\Task;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'clients' => Client::query()->count(),
            'activeClients' => Client::query()->where('is_active', true)->count(),
            'processes' => Process::query()->count(),
            'activeProcesses' => Process::query()
                ->whereIn('status', ['analysis', 'active', 'awaiting_decision'])
                ->count(),
            'deadlines' => Deadline::query()->count(),
            'deadlinesPending' => Deadline::pending()->count(),
            'deadlinesToday' => Deadline::dueToday()->count(),
            'deadlinesExpired' => Deadline::expired()->count(),
            'tasks' => Task::query()->count(),
            'tasksPending' => Task::query()
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'documents' => Document::query()->count(),
            'users' => User::query()->count(),
        ];

        $upcomingDeadlines = Deadline::query()
            ->with(['process:id,title,process_number'])
            ->pending()
            ->whereDate('due_date', '>=', today())
            ->orderBy('due_date')
            ->limit(6)
            ->get();

        $upcomingEvents = CalendarEvent::query()
            ->with(['process:id,title,process_number', 'client:id,name,company_name,type'])
            ->whereDate('start_datetime', '>=', today())
            ->orderBy('start_datetime')
            ->limit(6)
            ->get();

        $recentProcesses = Process::query()
            ->with(['client:id,name,company_name,type'])
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $stats,
            'upcomingDeadlines' => $upcomingDeadlines,
            'upcomingEvents' => $upcomingEvents,
            'recentProcesses' => $recentProcesses,
        ]);
    }
}