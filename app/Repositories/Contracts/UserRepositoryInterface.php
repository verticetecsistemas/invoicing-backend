<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Criar um novo usuário
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Buscar usuário por ID
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Buscar usuário por email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Listar todos os usuários
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Atualizar usuário
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * Deletar usuário
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
