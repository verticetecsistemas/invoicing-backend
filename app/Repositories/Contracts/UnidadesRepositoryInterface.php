<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface UnidadesRepositoryInterface
{
    public function all(): Collection;
}