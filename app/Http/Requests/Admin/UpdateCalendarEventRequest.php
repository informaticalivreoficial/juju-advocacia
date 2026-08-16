<?php

namespace App\Http\Requests\Admin;

use App\Enums\CalendarEventTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('calendar_event'));
    }

    public function rules(): array
    {
        return [
            'process_id' => ['nullable', 'integer', Rule::exists('processes', 'id')],
            'client_id' => ['nullable', 'integer', Rule::exists('clients', 'id')],
            'responsible_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(CalendarEventTypeEnum::class)],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['nullable', 'date', 'after_or_equal:start_datetime'],
            'all_day' => ['sometimes', 'boolean'],
            'location' => ['nullable', 'string', 'max:255'],
        ];
    }
}
