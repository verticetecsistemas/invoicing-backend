<?php

namespace App\Repositories\Contracts;

use App\Models\Convenios;
use Illuminate\Database\Eloquent\Collection;

interface ConveniosRepositoryInterface
{
    /**
     * Criar um novo convênio
     *
     * @param array $data
     * @return Convenios
     */
    public function create(array $data): Convenios;

    /**
     * Buscar convênio por ID
     *
     * @param int $id
     * @return Convenios|null
     */
    public function findById(int $id): ?Convenios;

    /**
     * Buscar convênio por CNPJ
     *
     * @param string $cnpj
     * @return Convenios|null
     */
    public function findByCnpj(string $cnpj): ?Convenios;

    /**
     * Listar todos os convênios
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Atualizar convênio
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Deletar convênio
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
