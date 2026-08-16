<?php

namespace App\Http\Requests\Admin;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    public function rules(): array
    {
        return [
            'deadline_id' => ['nullable', 'integer', Rule::exists('deadlines', 'id')],
            'client_id' => ['nullable', 'integer', Rule::exists('clients', 'id')],
            'process_id' => ['nullable', 'integer', Rule::exists('processes', 'id')],
            'responsible_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::enum(TaskStatusEnum::class)],
            'priority' => ['required', Rule::enum(TaskPriorityEnum::class)],
            'due_date' => ['nullable', 'date'],
        ];
    }
}
