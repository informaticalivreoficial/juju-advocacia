<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProcessAreaEnum;
use App\Enums\ProcessPriorityEnum;
use App\Enums\ProcessStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProcessRequest;
use App\Http\Requests\Admin\UpdateProcessRequest;
use App\Models\Client;
use App\Models\Process;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProcessController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Process::class);

        $processes = Process::query()
            ->with([
                'client:id,name,company_name,type',
                'responsibleUser:id,name',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->lower();

                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(process_number) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(title) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(plaintiff) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(defendant) like ?', ["%{$search}%"])
                        ->orWhereHas('client', fn ($c) => $c->whereRaw('LOWER(name) like ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(company_name) like ?', ["%{$search}%"]));
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->when($request->filled('area'), function ($query) use ($request) {
                $query->where('area', $request->string('area'));
            })
            ->when($request->filled('priority'), function ($query) use ($request) {
                $query->where('priority', $request->string('priority'));
            })
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Processes/Index', [
            'processes' => $processes,
            'filters' => $request->only(['search', 'status', 'area', 'priority']),
            'areas' => ProcessAreaEnum::options(),
            'statuses' => ProcessStatusEnum::options(),
            'priorities' => ProcessPriorityEnum::options(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Process::class);

        return Inertia::render('Admin/Processes/Create', [
            'areas' => ProcessAreaEnum::options(),
            'statuses' => ProcessStatusEnum::options(),
            'priorities' => ProcessPriorityEnum::options(),
            'instances' => $this->instances(),
            'users' => $this->users(),
            'clients' => $this->clients(),
        ]);
    }

    public function store(StoreProcessRequest $request): RedirectResponse
    {
        $process = Process::create($request->validated());

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Processo criado com sucesso.');
    }

    public function show(Process $process): Response
    {
        $this->authorize('view', $process);

        $process->load(['client:id,name,company_name,type', 'responsibleUser:id,name']);

        return Inertia::render('Admin/Processes/Show', [
            'process' => $process,
            'areas' => ProcessAreaEnum::options(),
            'statuses' => ProcessStatusEnum::options(),
            'priorities' => ProcessPriorityEnum::options(),
            // Timeline preparada para receber movimentações processuais futuramente.
            'timeline' => [],
        ]);
    }

    public function edit(Process $process): Response
    {
        $this->authorize('update', $process);

        return Inertia::render('Admin/Processes/Edit', [
            'process' => $process,
            'areas' => ProcessAreaEnum::options(),
            'statuses' => ProcessStatusEnum::options(),
            'priorities' => ProcessPriorityEnum::options(),
            'instances' => $this->instances(),
            'users' => $this->users(),
            'clients' => $this->clients(),
        ]);
    }

    public function update(UpdateProcessRequest $request, Process $process): RedirectResponse
    {
        $process->update($request->validated());

        return redirect()
            ->route('admin.processes.show', $process)
            ->with('success', 'Processo atualizado com sucesso.');
    }

    public function destroy(Process $process): RedirectResponse
    {
        $this->authorize('delete', $process);

        $process->delete();

        return redirect()
            ->route('admin.processes.index')
            ->with('success', 'Processo excluído.');
    }

    private function instances(): array
    {
        return [
            ['value' => 'first', 'label' => '1º grau'],
            ['value' => 'second', 'label' => '2º grau'],
            ['value' => 'superior', 'label' => 'Tribunal Superior'],
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
