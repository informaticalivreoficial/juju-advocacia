<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClientTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clientService) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->lower();
                $digits = preg_replace('/\D/', '', (string) $search);

                $query->where(function ($q) use ($search, $digits) {
                    $q->whereRaw('LOWER(name) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(company_name) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(email) like ?', ["%{$search}%"])
                        ->when($digits !== '', fn ($q2) => $q2->orWhere('document', $digits));
                });
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->string('type'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('is_active', $request->string('status') === 'active');
            })
            ->orderBy('name')
            ->orderBy('company_name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'type', 'status']),
            'types' => ClientTypeEnum::options(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('Admin/Clients/Create', [
            'types' => ClientTypeEnum::options(),
            'maritalStatuses' => $this->maritalStatuses(),
            'states' => $this->states(),
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = $this->clientService->create($request->validated());

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function show(Client $client): Response
    {
        $this->authorize('view', $client);

        return Inertia::render('Admin/Clients/Show', [
            'client' => $client,
        ]);
    }

    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        return Inertia::render('Admin/Clients/Edit', [
            'client' => $client,
            'types' => ClientTypeEnum::options(),
            'maritalStatuses' => $this->maritalStatuses(),
            'states' => $this->states(),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $this->clientService->update($client, $request->validated());

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'Cliente excluído.');
    }

    private function maritalStatuses(): array
    {
        return [
            ['value' => 'solteiro', 'label' => 'Solteiro(a)'],
            ['value' => 'casado', 'label' => 'Casado(a)'],
            ['value' => 'divorciado', 'label' => 'Divorciado(a)'],
            ['value' => 'viuvo', 'label' => 'Viúvo(a)'],
            ['value' => 'uniao_estavel', 'label' => 'União estável'],
            ['value' => 'outro', 'label' => 'Outro'],
        ];
    }

    private function states(): array
    {
        return collect([
            'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG',
            'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO',
        ])->map(fn ($uf) => ['value' => $uf, 'label' => $uf])->all();
    }
}
