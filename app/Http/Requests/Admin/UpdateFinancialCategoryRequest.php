<?php

namespace App\Http\Requests\Admin;

use App\Enums\FinancialType;
use App\Models\FinancialCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFinancialCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', FinancialCategory::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'type' => ['required', Rule::enum(FinancialType::class)],
            'color' => ['required', 'string', 'max:7'],
            'icon' => ['nullable', 'string', 'max:191'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome da categoria.',
            'type.required' => 'Informe o tipo da categoria.',
            'color.required' => 'Informe uma cor para a categoria.',
        ];
    }
}
