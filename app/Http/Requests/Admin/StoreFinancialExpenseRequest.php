<?php

namespace App\Http\Requests\Admin;

use App\Models\FinancialExpense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinancialExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', FinancialExpense::class);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('financial_categories', 'id')],
            'description' => ['required', 'string', 'max:191'],
            'due_day' => ['required', 'integer', 'between:1,28'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Selecione uma categoria.',
            'description.required' => 'Informe a descrição da despesa.',
            'due_day.required' => 'Informe o dia do vencimento.',
            'due_day.between' => 'O dia do vencimento deve estar entre 1 e 28.',
            'amount.required' => 'Informe o valor da despesa.',
        ];
    }
}
