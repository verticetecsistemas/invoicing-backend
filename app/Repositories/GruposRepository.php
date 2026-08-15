<?php

namespace App\Repositories;

use App\Models\Grupos;
use App\Repositories\Contracts\GruposRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GruposRepository implements GruposRepositoryInterface
{
    public function __construct(protected Grupos $model)
    {
    }

    public function all(): Collection
    {
        return $this->model->all();
    }
}