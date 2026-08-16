<?php

namespace App\Http\Requests\Admin;

use App\Enums\ClientTypeEnum;
use App\Models\Client;
use App\Rules\CpfCnpj;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Client::class);
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('document')) {
            $this->merge([
                'document' => preg_replace('/\D/', '', $this->input('document')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(ClientTypeEnum::class)],
            'name' => ['required_if:type,'.ClientTypeEnum::Individual->value, 'nullable', 'string', 'max:255'],
            'document' => ['required', new CpfCnpj, Rule::unique('clients', 'document')],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'profession' => ['nullable', 'string', 'max:255'],
            'company_name' => ['required_if:type,'.ClientTypeEnum::Company->value, 'nullable', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'state_registration' => ['nullable', 'string', 'max:50'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'size:2'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
