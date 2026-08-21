<?php

namespace App\Http\Requests\Admin;

use App\Models\FinancialBudget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinancialBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', FinancialBudget::class);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('financial_categories', 'id')],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'month' => ['required', 'integer', 'between:1,12'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Selecione uma categoria.',
            'amount.required' => 'Informe o valor do orçamento.',
        ];
    }
}
