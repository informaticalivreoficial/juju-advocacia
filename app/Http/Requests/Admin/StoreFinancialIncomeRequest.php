<?php

namespace App\Http\Requests\Admin;

use App\Models\FinancialIncome;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinancialIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', FinancialIncome::class);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('financial_categories', 'id')],
            'description' => ['required', 'string', 'max:191'],
            'receive_day' => ['required', 'integer', 'between:1,28'],
            'amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Selecione uma categoria.',
            'description.required' => 'Informe a descrição da receita.',
            'receive_day.required' => 'Informe o dia do recebimento.',
            'receive_day.between' => 'O dia do recebimento deve estar entre 1 e 28.',
            'amount.required' => 'Informe o valor da receita.',
        ];
    }
}
