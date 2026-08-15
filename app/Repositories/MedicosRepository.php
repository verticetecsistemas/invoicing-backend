<?php

namespace App\Repositories;

use App\Models\Medicos;
use App\Repositories\Contracts\MedicosRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class MedicosRepository implements MedicosRepositoryInterface
{
    public function __construct(protected Medicos $model)
    {
    }

    public function create(array $data): Medicos
    {
        return $this->model->create($data);
    }

    public function findById(int $id): ?Medicos
    {
        return $this->model->find($id);
    }

    public function all(): Collection
    {
        return $this->model
            ->with([
                'conselho:id,nome',
                'especialidade:id,nome',
            ])
            ->get();
    }

    public function update(int $id, array $data): bool
    {
        $medico = $this->findById($id);

        return $medico ? $medico->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $medico = $this->findById($id);

        return $medico ? (bool) $medico->delete() : false;
    }

    public function restore(int $id): bool
    {
        $medico = $this->findTrashedById($id);

        return $medico ? $medico->restore() : false;
    }

    public function findTrashedById(int $id): ?Medicos
    {
        return $this->model->onlyTrashed()->find($id);
    }
}