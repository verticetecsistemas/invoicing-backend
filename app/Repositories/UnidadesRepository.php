<?php

namespace App\Repositories;

use App\Models\Unidades;
use App\Repositories\Contracts\UnidadesRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UnidadesRepository implements UnidadesRepositoryInterface
{
    public function __construct(protected Unidades $model)
    {
    }

    public function all(): Collection
    {
        return $this->model->all();
    }
}