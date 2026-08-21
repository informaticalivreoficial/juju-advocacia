<?php

namespace App\Models;

use App\Enums\UserRoleEnum;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
        'last_login_at',
        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRoleEnum::class,
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function roleDefinition(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    public function responsibleProcesses(): HasMany
    {
        return $this->hasMany(Process::class, 'responsible_user_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRoleEnum::Admin;
    }

    public function hasRole(string|UserRoleEnum ...$roles): bool
    {
        foreach ($roles as $role) {
            $value = $role instanceof UserRoleEnum ? $role->value : $role;

            if ($this->role?->value === $value) {
                return true;
            }
        }

        return false;
    }

    public function permissions(): Collection
    {
        return $this->roleDefinition?->permissions ?? collect();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissions()->contains('name', $permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissions()->pluck('name')->intersect($permissions)->isNotEmpty();
    }
}
