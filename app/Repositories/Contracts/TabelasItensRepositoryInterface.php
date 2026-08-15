<?php

namespace App\Repositories\Contracts;

use App\Models\TabelasItens;
use Illuminate\Database\Eloquent\Collection;

interface TabelasItensRepositoryInterface
{
    public function create(array $data): TabelasItens;

    public function findById(int $id): ?TabelasItens;

    public function all(): Collection;

    public function findByTabelaId(int $tabelaId): Collection;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function restore(int $id): bool;

    public function findTrashedById(int $id): ?TabelasItens;
}