<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CalendarEventTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCalendarEventRequest;
use App\Http\Requests\Admin\UpdateCalendarEventRequest;
use App\Models\CalendarEvent;
use App\Models\Client;
use App\Models\Process;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CalendarEvent::class);

        $month = (int) $request->integer('month', now()->month);
        $year = (int) $request->integer('year', now()->year);

        $month = max(1, min(12, $month));
        $year = $year >= 1900 ? $year : now()->year;

        $firstDay = CarbonImmutable::createFromDate($year, $month, 1)->startOfDay();
        $lastDay = $firstDay->endOfMonth()->endOfDay();

        $events = CalendarEvent::query()
            ->with([
                'process:id,title,process_number',
                'client:id,name,company_name,type',
                'responsibleUser:id,name',
            ])
            ->between($firstDay, $lastDay)
            ->orderBy('start_datetime')
            ->get();

        return Inertia::render('Admin/Calendar/Index', [
            'events' => $events,
            'month' => $month,
            'year' => $year,
            'monthName' => $firstDay->translatedFormat('F'),
            'prev' => [
                'month' => $firstDay->subMonth()->month,
                'year' => $firstDay->subMonth()->year,
            ],
            'next' => [
                'month' => $firstDay->addMonth()->month,
                'year' => $firstDay->addMonth()->year,
            ],
            'types' => CalendarEventTypeEnum::options(),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', CalendarEvent::class);

        return Inertia::render('Admin/Calendar/Create', [
            'types' => CalendarEventTypeEnum::options(),
            'users' => $this->users(),
            'processes' => $this->processes(),
            'clients' => $this->clients(),
            'date' => $request->query('date'),
        ]);
    }

    public function store(StoreCalendarEventRequest $request): RedirectResponse
    {
        CalendarEvent::create($request->validated());

        return redirect()
            ->route('admin.calendar.index')
            ->with('success', 'Evento criado com sucesso.');
    }

    public function edit(CalendarEvent $calendar_event): Response
    {
        $this->authorize('update', $calendar_event);

        return Inertia::render('Admin/Calendar/Edit', [
            'event' => $calendar_event,
            'types' => CalendarEventTypeEnum::options(),
            'users' => $this->users(),
            'processes' => $this->processes(),
            'clients' => $this->clients(),
        ]);
    }

    public function update(UpdateCalendarEventRequest $request, CalendarEvent $calendar_event): RedirectResponse
    {
        $calendar_event->update($request->validated());

        return redirect()
            ->route('admin.calendar.index')
            ->with('success', 'Evento atualizado com sucesso.');
    }

    public function destroy(CalendarEvent $calendar_event): RedirectResponse
    {
        $this->authorize('delete', $calendar_event);

        $calendar_event->delete();

        return redirect()
            ->route('admin.calendar.index')
            ->with('success', 'Evento excluído.');
    }

    private function users(): array
    {
        return User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => [
                'value' => $user->id,
                'label' => $user->name,
            ])
            ->all();
    }

    private function processes(): array
    {
        return Process::query()
            ->whereNull('deleted_at')
            ->orderBy('title')
            ->get(['id', 'title', 'process_number'])
            ->map(fn (Process $process) => [
                'value' => $process->id,
                'label' => $process->process_number
                    ? "{$process->title} — {$process->process_number}"
                    : $process->title,
            ])
            ->all();
    }

    private function clients(): array
    {
        return Client::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->orderBy('company_name')
            ->get()
            ->map(fn (Client $client) => [
                'value' => $client->id,
                'label' => $client->displayName(),
            ])
            ->all();
    }
}
