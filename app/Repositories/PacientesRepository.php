<?php

namespace App\Repositories;

use App\Models\Pacientes;
use App\Repositories\Contracts\PacientesRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PacientesRepository implements PacientesRepositoryInterface
{
    public function __construct(protected Pacientes $model)
    {
    }

    public function create(array $data): Pacientes
    {
        return $this->model->create($data);
    }

    public function findById(int $id): ?Pacientes
    {
        return $this->model->find($id);
    }

    public function all(): Collection
    {
        return $this->model
            ->with('convenio:id,razao_social')
            ->orderBy('nome', 'asc')
            ->get();
    }

    public function update(int $id, array $data): bool
    {
        $paciente = $this->findById($id);

        return $paciente ? $paciente->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $paciente = $this->findById($id);

        return $paciente ? (bool) $paciente->delete() : false;
    }

    public function restore(int $id): bool
    {
        $paciente = $this->findTrashedById($id);

        return $paciente ? $paciente->restore() : false;
    }

    public function findTrashedById(int $id): ?Pacientes
    {
        return $this->model->onlyTrashed()->find($id);
    }
}