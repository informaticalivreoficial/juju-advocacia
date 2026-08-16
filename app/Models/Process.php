<?php

namespace App\Models;

use App\Enums\ProcessAreaEnum;
use App\Enums\ProcessPriorityEnum;
use App\Enums\ProcessStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Process extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'client_id',
        'responsible_user_id',
        'process_number',
        'title',
        'area',
        'action_type',
        'court',
        'district',
        'court_division',
        'instance',
        'plaintiff',
        'defendant',
        'case_value',
        'distribution_date',
        'status',
        'priority',
        'confidentiality',
        'description',
    ];

    protected $casts = [
        'status' => ProcessStatusEnum::class,
        'priority' => ProcessPriorityEnum::class,
        'area' => ProcessAreaEnum::class,
        'case_value' => 'decimal:2',
        'distribution_date' => 'date',
        'confidentiality' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Process $process) {
            if (empty($process->uuid)) {
                $process->uuid = (string) Str::uuid();
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
