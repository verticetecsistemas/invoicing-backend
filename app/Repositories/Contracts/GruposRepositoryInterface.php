<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface GruposRepositoryInterface
{
    public function all(): Collection;
}