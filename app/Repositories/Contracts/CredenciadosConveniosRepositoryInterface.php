<?php

namespace App\Repositories\Contracts;

use App\Models\CredenciadosConvenios;
use Illuminate\Database\Eloquent\Collection;

interface CredenciadosConveniosRepositoryInterface
{
    public function create(array $data): CredenciadosConvenios;

    public function findById(int $id): ?CredenciadosConvenios;

    public function findTrashedById(int $id): ?CredenciadosConvenios;

    public function all(): Collection;

    public function findByCredenciadoId(int $credenciadoId): Collection;

    public function findNamesByCredenciadoId(int $credenciadoId): array;

    public function findByCredenciadoAndConvenio(int $credenciadoId, int $convenioId): ?CredenciadosConvenios;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function restore(int $id): bool;
}
