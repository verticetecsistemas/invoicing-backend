<?php

namespace App\Repositories;

use App\Models\Tabelas;
use App\Repositories\Contracts\TabelasRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TabelasRepository implements TabelasRepositoryInterface
{
    /**
     * @var Tabelas
     */
    protected $model;

    /**
     * TabelasRepository constructor.
     *
     * @param Tabelas $model
     */
    public function __construct(Tabelas $model)
    {
        $this->model = $model;
    }

    /**
     * Criar uma nova tabela
     *
     * @param array $data
     * @return Tabelas
     */
    public function create(array $data): Tabelas
    {
        return $this->model->create($data);
    }

    /**
     * Buscar tabela por ID
     *
     * @param int $id
     * @return Tabelas|null
     */
    public function findById(int $id): ?Tabelas
    {
        return $this->model->find($id);
    }

    /**
     * Listar todas as tabelas
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Atualizar tabela
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $tabela = $this->model->find($id);
        
        if (!$tabela) {
            return false;
        }

        return $tabela->update($data);
    }

    /**
     * Deletar tabela (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $tabela = $this->model->find($id);
        
        if (!$tabela) {
            return false;
        }

        return $tabela->delete();
    }

    /**
     * Restaurar tabela deletada
     *
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool
    {
        $tabela = $this->model->onlyTrashed()->find($id);
        
        if (!$tabela) {
            return false;
        }

        return $tabela->restore();
    }

    /**
     * Buscar tabela deletada por ID
     *
     * @param int $id
     * @return Tabelas|null
     */
    public function findTrashedById(int $id): ?Tabelas
    {
        return $this->model->onlyTrashed()->find($id);
    }
}
