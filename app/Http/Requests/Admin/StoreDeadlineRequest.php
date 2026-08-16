<?php

namespace App\Http\Requests\Admin;

use App\Enums\DeadlinePriorityEnum;
use App\Enums\DeadlineStatusEnum;
use App\Models\Deadline;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeadlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Deadline::class);
    }

    public function rules(): array
    {
        return [
            'process_id' => ['nullable', 'integer', Rule::exists('processes', 'id')],
            'responsible_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['required', 'date'],
            'status' => ['required', Rule::enum(DeadlineStatusEnum::class)],
            'priority' => ['required', Rule::enum(DeadlinePriorityEnum::class)],
        ];
    }
}
