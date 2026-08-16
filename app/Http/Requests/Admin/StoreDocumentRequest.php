<?php

namespace App\Http\Requests\Admin;

use App\Enums\DocumentCategoryEnum;
use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Document::class);
    }

    public function rules(): array
    {
        return [
            'process_id' => ['nullable', 'integer', Rule::exists('processes', 'id')],
            'client_id' => ['nullable', 'integer', Rule::exists('clients', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['required', Rule::enum(DocumentCategoryEnum::class)],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Selecione um arquivo para enviar.',
            'file.mimes' => 'O arquivo deve ser PDF, documento (doc/docx), planilha (xls/xlsx), imagem (jpg/png) ou ZIP.',
            'file.max' => 'O arquivo deve ter no máximo 20 MB.',
        ];
    }
}
