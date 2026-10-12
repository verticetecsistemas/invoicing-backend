<?php

namespace App\Repositories;

use App\Models\CredenciadosConvenios;
use App\Repositories\Contracts\CredenciadosConveniosRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CredenciadosConveniosRepository implements CredenciadosConveniosRepositoryInterface
{
    public function __construct(protected CredenciadosConvenios $model)
    {
    }

    public function create(array $data): CredenciadosConvenios
    {
        return $this->model->create($data);
    }

    public function findById(int $id): ?CredenciadosConvenios
    {
        return $this->model->with(['tabelas', 'convenio'])->find($id);
    }

    public function findTrashedById(int $id): ?CredenciadosConvenios
    {
        return $this->model->onlyTrashed()->find($id);
    }

    public function all(): Collection
    {
        return $this->model->with(['tabelas', 'convenio'])->get();
    }

    public function findByCredenciadoId(int $credenciadoId): Collection
    {
        return $this->model->with(['tabelas', 'convenio'])
            ->where('credenciado_id', $credenciadoId)
            ->get();
    }

    public function findByCredenciadoAndConvenio(int $credenciadoId, int $convenioId): ?CredenciadosConvenios
    {
        return $this->model->with(['tabelas', 'convenio'])
            ->where('credenciado_id', $credenciadoId)
            ->where('convenio_id', $convenioId)
            ->first();
    }

    public function update(int $id, array $data): bool
    {
        $credenciadoConvenio = $this->model->find($id);

        return $credenciadoConvenio ? $credenciadoConvenio->update($data) : false;
    }

    public function delete(int $id): bool
    {
        $credenciadoConvenio = $this->model->find($id);

        return $credenciadoConvenio ? (bool) $credenciadoConvenio->delete() : false;
    }

    public function restore(int $id): bool
    {
        $credenciadoConvenio = $this->findTrashedById($id);

        return $credenciadoConvenio ? $credenciadoConvenio->restore() : false;
    }
}
