<?php

namespace App\Repositories;

use App\Models\Convenios;
use App\Repositories\Contracts\ConveniosRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ConveniosRepository implements ConveniosRepositoryInterface
{
    /**
     * @var Convenios
     */
    protected $model;

    /**
     * ConveniosRepository constructor.
     *
     * @param Convenios $model
     */
    public function __construct(Convenios $model)
    {
        $this->model = $model;
    }

    /**
     * Criar um novo convênio
     *
     * @param array $data
     * @return Convenios
     */
    public function create(array $data): Convenios
    {
        return $this->model->create($data);
    }

    /**
     * Buscar convênio por ID
     *
     * @param int $id
     * @return Convenios|null
     */
    public function findById(int $id): ?Convenios
    {
        return $this->model->find($id);
    }

    /**
     * Buscar convênio por CNPJ
     *
     * @param string $cnpj
     * @return Convenios|null
     */
    public function findByCnpj(string $cnpj): ?Convenios
    {
        return $this->model->where('cnpj', $cnpj)->first();
    }

    /**
     * Listar todos os convênios
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Atualizar convênio
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $convenio = $this->model->find($id);
        
        if (!$convenio) {
            return false;
        }

        return $convenio->update($data);
    }

    /**
     * Deletar convênio
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $convenio = $this->model->find($id);
        
        if (!$convenio) {
            return false;
        }

        return $convenio->delete();
    }
}
