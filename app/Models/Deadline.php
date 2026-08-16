<?php

namespace App\Models;

use App\Enums\DeadlinePriorityEnum;
use App\Enums\DeadlineStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Deadline extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'process_id',
        'responsible_user_id',
        'title',
        'description',
        'start_date',
        'due_date',
        'completed_at',
        'status',
        'priority',
    ];

    protected $casts = [
        'status' => DeadlineStatusEnum::class,
        'priority' => DeadlinePriorityEnum::class,
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Deadline $deadline) {
            if (empty($deadline->uuid)) {
                $deadline->uuid = (string) Str::uuid();
            }
        });
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * Status efetivo considerando o vencimento do prazo.
     * Um prazo pendente/em andamento com data de vencimento passada é Vencido.
     */
    public function effectiveStatus(): DeadlineStatusEnum
    {
        if (! in_array($this->status, [DeadlineStatusEnum::Pending, DeadlineStatusEnum::InProgress])) {
            return $this->status;
        }

        return $this->due_date?->isBefore(today())
            ? DeadlineStatusEnum::Expired
            : $this->status;
    }

    public function isExpired(): bool
    {
        return $this->effectiveStatus() === DeadlineStatusEnum::Expired;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [
            DeadlineStatusEnum::Pending->value,
            DeadlineStatusEnum::InProgress->value,
        ]);
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->pending()->whereDate('due_date', today());
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->pending()->whereDate('due_date', '>=', today());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->pending()->whereDate('due_date', '<', today());
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', DeadlineStatusEnum::Completed->value);
    }
}
