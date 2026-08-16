<?php

namespace App\Models;

use App\Enums\ClientTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'type',
        'name',
        'document',
        'email',
        'phone',
        'mobile',
        'birth_date',
        'marital_status',
        'profession',
        'company_name',
        'trade_name',
        'state_registration',
        'zip_code',
        'address',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'type' => ClientTypeEnum::class,
        'is_active' => 'boolean',
        'birth_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->uuid)) {
                $client->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Nome exibido conforme o tipo (nome ou razão social).
     */
    public function displayName(): string
    {
        return $this->type === ClientTypeEnum::Company
            ? ($this->company_name ?? $this->name)
            : ($this->name ?? '');
    }

    public function processes(): HasMany
    {
        return $this->hasMany(Process::class);
    }
}
