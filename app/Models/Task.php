<?php

namespace App\Models;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'deadline_id',
        'client_id',
        'process_id',
        'responsible_user_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'status' => TaskStatusEnum::class,
        'priority' => TaskPriorityEnum::class,
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (empty($task->uuid)) {
                $task->uuid = (string) Str::uuid();
            }
        });
    }

    public function deadline(): BelongsTo
    {
        return $this->belongsTo(Deadline::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === TaskStatusEnum::Completed;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', TaskStatusEnum::Pending->value);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', TaskStatusEnum::InProgress->value);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', TaskStatusEnum::Completed->value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            TaskStatusEnum::Pending->value,
            TaskStatusEnum::InProgress->value,
        ]);
    }
}
