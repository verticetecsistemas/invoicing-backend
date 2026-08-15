<?php

namespace App\Repositories;

use App\Models\TabelasItens;
use App\Repositories\Contracts\TabelasItensRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TabelasItensRepository implements TabelasItensRepositoryInterface
{
    public function __construct(protected TabelasItens $model)
    {
    }

    public function create(array $data): TabelasItens
    {
        return $this->model->create($data);
    }

    public function findById(int $id): ?TabelasItens
    {
        return $this->model->find($id);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function findByTabelaId(int $tabelaId): Collection
    {
        return $this->model->where('tabelas_id', $tabelaId)->get();
    }

    public function update(int $id, array $data): bool
    {
        $item = $this->findById($id);

        return $item ? $item->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $item = $this->findById($id);

        return $item ? (bool) $item->delete() : false;
    }

    public function restore(int $id): bool
    {
        $item = $this->findTrashedById($id);

        return $item ? $item->restore() : false;
    }

    public function findTrashedById(int $id): ?TabelasItens
    {
        return $this->model->onlyTrashed()->find($id);
    }
}