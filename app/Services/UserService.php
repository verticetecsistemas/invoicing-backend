<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * UserService constructor.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Criar um novo usuário
     *
     * @param array $data
     * @return User
     * @throws ValidationException
     */
    public function createUser(array $data): User
    {
        // Validar dados
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8',
            'tipo' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Verificar se email já existe
        if ($this->userRepository->findByEmail($data['email'])) {
            throw ValidationException::withMessages([
                'email' => ['O email já está em uso.']
            ]);
        }

        // Criar usuário
        return $this->userRepository->create($data);
    }

    /**
     * Buscar usuário por ID
     *
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * Buscar usuário por email
     *
     * @param string $email
     * @return User|null
     */
    public function getUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Listar todos os usuários
     *
     * @return Collection
     */
    public function getAllUsers(): Collection
    {
        return $this->userRepository->all();
    }

    /**
     * Atualizar usuário
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws ValidationException
     */
    public function updateUser(int $id, array $data): bool
    {
        // Validar dados
        $validator = Validator::make($data, [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
            'tipo' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Verificar se usuário existe
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw ValidationException::withMessages([
                'id' => ['Usuário não encontrado.']
            ]);
        }

        // Atualizar usuário
        return $this->userRepository->update($id, $data);
    }

    /**
     * Deletar usuário
     *
     * @param int $id
     * @return bool
     * @throws ValidationException
     */
    public function deleteUser(int $id): bool
    {
        // Verificar se usuário existe
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw ValidationException::withMessages([
                'id' => ['Usuário não encontrado.']
            ]);
        }

        return $this->userRepository->delete($id);
    }
}
