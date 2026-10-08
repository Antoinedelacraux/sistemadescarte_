<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class FundoScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!Auth::check()) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();

        // Admin y Analista tienen acceso a todos los fundos sin restricción
        if ($user->isAdmin() || $user->isAnalista()) {
            return;
        }

        // Roles General e Individual solo ven los fundos a los que están asignados
        $fundoIds = $user->fundos()->pluck('fundos.id')->toArray();

        $builder->whereIn($model->getTable() . '.fundo_id', $fundoIds);
    }
}
