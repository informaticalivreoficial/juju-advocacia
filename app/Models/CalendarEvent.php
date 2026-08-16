<?php

namespace App\Models;

use App\Enums\CalendarEventTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'process_id',
        'client_id',
        'responsible_user_id',
        'title',
        'description',
        'type',
        'start_datetime',
        'end_datetime',
        'all_day',
        'location',
    ];

    protected $casts = [
        'type' => CalendarEventTypeEnum::class,
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'all_day' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (CalendarEvent $event) {
            if (empty($event->uuid)) {
                $event->uuid = (string) Str::uuid();
            }
        });
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function scopeBetween(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('start_datetime', [$start, $end]);
    }
}
