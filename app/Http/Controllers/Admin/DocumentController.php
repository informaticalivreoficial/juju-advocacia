<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DocumentCategoryEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentRequest;
use App\Models\Client;
use App\Models\Document;
use App\Models\Process;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Document::class);

        $documents = Document::query()
            ->with([
                'process:id,title,process_number',
                'client:id,name,company_name,type',
                'uploader:id,name',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->trim()->lower();

                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(title) like ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(file_name) like ?', ["%{$search}%"])
                        ->orWhereHas('process', fn ($p) => $p->whereRaw('LOWER(title) like ?', ["%{$search}%"]))
                        ->orWhereHas('client', fn ($c) => $c->whereRaw('LOWER(name) like ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(company_name) like ?', ["%{$search}%"]));
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->where('category', $request->string('category'));
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Documents/Index', [
            'documents' => $documents,
            'filters' => $request->only(['search', 'category']),
            'categories' => DocumentCategoryEnum::options(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Document::class);

        return Inertia::render('Admin/Documents/Create', [
            'categories' => DocumentCategoryEnum::options(),
            'processes' => $this->processes(),
            'clients' => $this->clients(),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('documents');

        Document::create([
            'process_id' => $request->input('process_id'),
            'client_id' => $request->input('client_id'),
            'uploaded_by' => $request->user()->id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return redirect()
            ->route('admin.documents.index')
            ->with('success', 'Documento enviado com sucesso.');
    }

    public function download(Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        abort_unless(Storage::exists($document->file_path), 404);

        return Storage::download($document->file_path, $document->downloadName());
    }

    public function destroy(Document $document): RedirectResponse
    {
        $this->authorize('delete', $document);

        $document->deleteFile();
        $document->delete();

        return redirect()
            ->route('admin.documents.index')
            ->with('success', 'Documento excluído.');
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
