<?php

namespace App\Repositories\Contracts;

use App\Models\Medicos;
use Illuminate\Database\Eloquent\Collection;

interface MedicosRepositoryInterface
{
    public function create(array $data): Medicos;

    public function findById(int $id): ?Medicos;

    public function all(): Collection;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function restore(int $id): bool;

    public function findTrashedById(int $id): ?Medicos;
}