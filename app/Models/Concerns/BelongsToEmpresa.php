<?php

namespace App\Models\Concerns;

use App\Models\Empresas;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToEmpresa
{
    protected static function bootBelongsToEmpresa(): void
    {
        static::addGlobalScope('empresa', function (Builder $builder) {
            $user = Auth::user();

            if (!$user) {
                return;
            }

            $column = $builder->getModel()->qualifyColumn('empresa_id');

            if ($user->empresa_id) {
                $builder->where($column, $user->empresa_id);
            } else {
                $builder->whereNull($column);
            }
        });

        static::creating(function ($model) {
            $user = Auth::user();

            if ($user && $user->empresa_id) {
                $model->empresa_id = $user->empresa_id;
            }
        });
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresas::class, 'empresa_id');
    }
}