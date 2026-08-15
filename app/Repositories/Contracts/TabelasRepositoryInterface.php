<?php

namespace App\Repositories\Contracts;

use App\Models\Tabelas;
use Illuminate\Database\Eloquent\Collection;

interface TabelasRepositoryInterface
{
    /**
     * Criar uma nova tabela
     *
     * @param array $data
     * @return Tabelas
     */
    public function create(array $data): Tabelas;

    /**
     * Buscar tabela por ID
     *
     * @param int $id
     * @return Tabelas|null
     */
    public function findById(int $id): ?Tabelas;

    /**
     * Listar todas as tabelas
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Atualizar tabela
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Deletar tabela (soft delete)
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Restaurar tabela deletada
     *
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool;

    /**
     * Buscar tabela deletada por ID
     *
     * @param int $id
     * @return Tabelas|null
     */
    public function findTrashedById(int $id): ?Tabelas;
}
