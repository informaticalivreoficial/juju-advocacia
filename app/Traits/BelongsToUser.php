<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Restringe as consultas do modelo ao usuário autenticado.
 *
 * Em contextos sem autenticação (commands, webhooks), utilize
 * ->withoutGlobalScope('user') para ignorar o filtro.
 */
trait BelongsToUser
{
    protected static function bootBelongsToUser(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            $builder->where($builder->qualifyColumn('user_id'), auth()->id());
        });
    }
}
