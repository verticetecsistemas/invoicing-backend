<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @var User
     */
    protected $model;

    /**
     * UserRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Criar um novo usuário
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        // Hash da senha se fornecida
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $empresaId = Auth::user()?->empresa_id;
        if ($empresaId) {
            $data['empresa_id'] = $empresaId;
        }

        return $this->model->create($data);
    }

    /**
     * Buscar usuário por ID
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return $this->scopedQuery()->find($id);
    }

    /**
     * Buscar usuário por email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Listar todos os usuários
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->scopedQuery()->get();
    }

    /**
     * Atualizar usuário
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $user = $this->findById($id);
        
        if (!$user) {
            return false;
        }

        // Hash da senha se fornecida
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $user->update($data);
    }

    /**
     * Deletar usuário
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        
        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    private function scopedQuery(): Builder
    {
        $query = $this->model->newQuery();
        $logado = Auth::user();

        if ($logado) {
            $logado->empresa_id
                ? $query->where('empresa_id', $logado->empresa_id)
                : $query->whereNull('empresa_id');
        }

        return $query;
    }
}
