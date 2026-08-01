<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserController constructor.
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Listar todos os usuários
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $users = $this->userService->getAllUsers();
            
            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao listar usuários: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar usuários.'
            ], 500);
        }
    }

    /**
     * Criar um novo usuário
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->all());
            
            Log::info('Usuário criado com sucesso', ['user_id' => $user->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'tipo' => $user->tipo,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao criar usuário: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar usuário.'
            ], 500);
        }
    }

    /**
     * Buscar usuário por ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado.'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar usuário: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar usuário.'
            ], 500);
        }
    }

    /**
     * Atualizar usuário
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $updated = $this->userService->updateUser($id, $request->all());
            
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar usuário.'
                ], 500);
            }
            
            Log::info('Usuário atualizado com sucesso', ['user_id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso.'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário.'
            ], 500);
        }
    }

    /**
     * Deletar usuário
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->userService->deleteUser($id);
            
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao deletar usuário.'
                ], 500);
            }
            
            Log::info('Usuário deletado com sucesso', ['user_id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário deletado com sucesso.'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar usuário: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar usuário.'
            ], 500);
        }
    }
}
