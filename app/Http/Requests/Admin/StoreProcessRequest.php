<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProcessAreaEnum;
use App\Enums\ProcessPriorityEnum;
use App\Enums\ProcessStatusEnum;
use App\Models\Process;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Process::class);
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', Rule::exists('clients', 'id')],
            'responsible_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'process_number' => ['nullable', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'area' => ['required', Rule::enum(ProcessAreaEnum::class)],
            'action_type' => ['nullable', 'string', 'max:255'],
            'court' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'court_division' => ['nullable', 'string', 'max:255'],
            'instance' => ['nullable', 'string', 'max:50'],
            'plaintiff' => ['nullable', 'string', 'max:255'],
            'defendant' => ['nullable', 'string', 'max:255'],
            'case_value' => ['nullable', 'numeric', 'min:0'],
            'distribution_date' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(ProcessStatusEnum::class)],
            'priority' => ['required', Rule::enum(ProcessPriorityEnum::class)],
            'confidentiality' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
        ];
    }
}
