<?php

namespace App\Repositories\Contracts;

use App\Models\Pacientes;
use Illuminate\Database\Eloquent\Collection;

interface PacientesRepositoryInterface
{
    public function create(array $data): Pacientes;

    public function findById(int $id): ?Pacientes;

    public function all(): Collection;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function restore(int $id): bool;

    public function findTrashedById(int $id): ?Pacientes;
}