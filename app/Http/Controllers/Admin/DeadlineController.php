<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DeadlinePriorityEnum;
use App\Enums\DeadlineStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDeadlineRequest;
use App\Http\Requests\Admin\UpdateDeadlineRequest;
use App\Models\Deadline;
use App\Models\Process;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeadlineController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Deadline::class);

        $deadlines = Deadline::query()
            ->with([
                'process:id,title,process_number',
                'responsibleUser:id,name',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->lower();

                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(title) like ?', ["%{$search}%"])
                        ->orWhereHas('process', fn ($p) => $p->whereRaw('LOWER(title) like ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(process_number) like ?', ["%{$search}%"]));
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $this->applyStatusFilter($query, $request->string('status'));
            })
            ->when($request->filled('priority'), function ($query) use ($request) {
                $query->where('priority', $request->string('priority'));
            })
            ->orderBy('due_date')
            ->paginate(15)
            ->through(fn (Deadline $deadline) => $deadline->setAttribute('effective_status', $deadline->effectiveStatus()->value))
            ->withQueryString();

        return Inertia::render('Admin/Deadlines/Index', [
            'deadlines' => $deadlines,
            'filters' => $request->only(['search', 'status', 'priority']),
            'statuses' => $this->statusOptions(),
            'priorities' => DeadlinePriorityEnum::options(),
            'users' => $this->users(),
            'processes' => $this->processes(),
            'counts' => [
                'pending' => Deadline::pending()->count(),
                'today' => Deadline::dueToday()->count(),
                'upcoming' => Deadline::upcoming()->count(),
                'expired' => Deadline::expired()->count(),
                'completed' => Deadline::completed()->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Deadline::class);

        return Inertia::render('Admin/Deadlines/Create', [
            'statuses' => DeadlineStatusEnum::options(),
            'priorities' => DeadlinePriorityEnum::options(),
            'users' => $this->users(),
            'processes' => $this->processes(),
        ]);
    }

    public function store(StoreDeadlineRequest $request): RedirectResponse
    {
        Deadline::create($request->validated());

        return redirect()
            ->route('admin.deadlines.index')
            ->with('success', 'Prazo criado com sucesso.');
    }

    public function edit(Deadline $deadline): Response
    {
        $this->authorize('update', $deadline);

        return Inertia::render('Admin/Deadlines/Edit', [
            'deadline' => $deadline,
            'statuses' => DeadlineStatusEnum::options(),
            'priorities' => DeadlinePriorityEnum::options(),
            'users' => $this->users(),
            'processes' => $this->processes(),
        ]);
    }

    public function update(UpdateDeadlineRequest $request, Deadline $deadline): RedirectResponse
    {
        $deadline->update($request->validated());

        return redirect()
            ->route('admin.deadlines.index')
            ->with('success', 'Prazo atualizado com sucesso.');
    }

    public function destroy(Deadline $deadline): RedirectResponse
    {
        $this->authorize('delete', $deadline);

        $deadline->delete();

        return redirect()
            ->route('admin.deadlines.index')
            ->with('success', 'Prazo excluído.');
    }

    public function toggleComplete(Deadline $deadline): RedirectResponse
    {
        $this->authorize('update', $deadline);

        if ($deadline->status === DeadlineStatusEnum::Completed) {
            $deadline->update([
                'status' => DeadlineStatusEnum::Pending,
                'completed_at' => null,
            ]);
        } else {
            $deadline->update([
                'status' => DeadlineStatusEnum::Completed,
                'completed_at' => now(),
            ]);
        }

        return redirect()
            ->route('admin.deadlines.index')
            ->with('success', 'Prazo atualizado com sucesso.');
    }

    private function applyStatusFilter($query, string $status): void
    {
        match ($status) {
            'pending' => $query->pending(),
            'today' => $query->dueToday(),
            'upcoming' => $query->upcoming(),
            'expired' => $query->expired(),
            'completed' => $query->completed(),
            default => null,
        };
    }

    private function statusOptions(): array
    {
        return [
            ['value' => 'pending', 'label' => 'Pendentes'],
            ['value' => 'today', 'label' => 'Vencem hoje'],
            ['value' => 'upcoming', 'label' => 'Próximos'],
            ['value' => 'expired', 'label' => 'Vencidos'],
            ['value' => 'completed', 'label' => 'Concluídos'],
        ];
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
}
