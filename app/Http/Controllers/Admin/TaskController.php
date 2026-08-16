<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTaskRequest;
use App\Http\Requests\Admin\UpdateTaskRequest;
use App\Models\Client;
use App\Models\Deadline;
use App\Models\Process;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Task::class);

        $tasks = Task::query()
            ->with([
                'deadline:id,title,due_date',
                'client:id,name,company_name,type',
                'process:id,title,process_number',
                'responsibleUser:id,name',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->lower();

                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(title) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(description) like ?', ["%{$search}%"])
                        ->orWhereHas('process', fn ($p) => $p->whereRaw('LOWER(title) like ?', ["%{$search}%"]))
                        ->orWhereHas('client', fn ($c) => $c->whereRaw('LOWER(name) like ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(company_name) like ?', ["%{$search}%"]));
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->when($request->filled('priority'), function ($query) use ($request) {
                $query->where('priority', $request->string('priority'));
            })
            ->orderBy('due_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only(['search', 'status', 'priority']),
            'statuses' => $this->statusOptions(),
            'priorities' => TaskPriorityEnum::options(),
            'counts' => [
                'pending' => Task::pending()->count(),
                'in_progress' => Task::inProgress()->count(),
                'completed' => Task::completed()->count(),
            ],
            'users' => $this->users(),
            'processes' => $this->processes(),
            'clients' => $this->clients(),
            'deadlines' => $this->deadlines(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Task::class);

        return Inertia::render('Admin/Tasks/Create', [
            'statuses' => TaskStatusEnum::options(),
            'priorities' => TaskPriorityEnum::options(),
            'users' => $this->users(),
            'processes' => $this->processes(),
            'clients' => $this->clients(),
            'deadlines' => $this->deadlines(),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        Task::create($request->validated());

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Tarefa criada com sucesso.');
    }

    public function edit(Task $task): Response
    {
        $this->authorize('update', $task);

        return Inertia::render('Admin/Tasks/Edit', [
            'task' => $task,
            'statuses' => TaskStatusEnum::options(),
            'priorities' => TaskPriorityEnum::options(),
            'users' => $this->users(),
            'processes' => $this->processes(),
            'clients' => $this->clients(),
            'deadlines' => $this->deadlines(),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->validated());

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Tarefa atualizada com sucesso.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Tarefa excluída.');
    }

    public function toggleComplete(Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        if ($task->isCompleted()) {
            $task->update([
                'status' => TaskStatusEnum::Pending,
                'completed_at' => null,
            ]);
        } else {
            $task->update([
                'status' => TaskStatusEnum::Completed,
                'completed_at' => now(),
            ]);
        }

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'Tarefa atualizada com sucesso.');
    }

    private function statusOptions(): array
    {
        return [
            ['value' => 'pending', 'label' => 'Pendentes'],
            ['value' => 'in_progress', 'label' => 'Em andamento'],
            ['value' => 'completed', 'label' => 'Concluídas'],
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

    private function deadlines(): array
    {
        return Deadline::query()
            ->orderBy('due_date')
            ->limit(200)
            ->get(['id', 'title', 'due_date'])
            ->map(fn (Deadline $deadline) => [
                'value' => $deadline->id,
                'label' => "{$deadline->title} ({$deadline->due_date?->format('d/m/Y')})",
            ])
            ->all();
    }
}
