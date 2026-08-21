<?php

namespace App\Http\Requests\Admin;

use App\Enums\FinancialPaymentMethod;
use App\Enums\FinancialStatus;
use App\Enums\FinancialType;
use App\Models\FinancialTransaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinancialTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', FinancialTransaction::class);
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(FinancialType::class)],
            'category_id' => ['required', 'integer', Rule::exists('financial_categories', 'id')],
            'description' => ['nullable', 'string', 'max:191'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'month' => ['required', 'integer', 'between:1,12'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(FinancialStatus::class)],
            'payment_method' => ['nullable', Rule::enum(FinancialPaymentMethod::class)],
            'due_date' => ['nullable', 'date'],
            'paid_at' => ['nullable', 'date'],
            'received_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Informe o tipo do lançamento.',
            'category_id.required' => 'Selecione uma categoria.',
            'year.required' => 'Informe o ano.',
            'month.required' => 'Informe o mês.',
            'amount.required' => 'Informe o valor do lançamento.',
            'status.required' => 'Informe o status do lançamento.',
            'attachment.max' => 'O comprovante deve ter no máximo 2 MB.',
        ];
    }
}
