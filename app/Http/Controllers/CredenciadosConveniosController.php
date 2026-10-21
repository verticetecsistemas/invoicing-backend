<?php

namespace App\Http\Controllers;

use App\Services\CredenciadosConveniosService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class CredenciadosConveniosController extends Controller
{
    public function __construct(protected CredenciadosConveniosService $credenciadosConveniosService)
    {
    }

    /**
     * Listar os convênios de um credenciado
     *
     * @param int $credenciadoId
     * @return JsonResponse
     */
    public function index(int $credenciadoId): JsonResponse
    {
        try {
            $credenciadosConvenios = $this->credenciadosConveniosService->getAllCredenciadosConvenios($credenciadoId);

            return response()->json([
                'success' => true,
                'data' => $credenciadosConvenios
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao listar convênios dos credenciados: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar convênios dos credenciados.'
            ], 500);
        }
    }

    /**
     * Listar apenas os nomes dos convênios de um credenciado
     */
    public function names(int $credenciadoId): JsonResponse
    {
        try {
            $nomes = $this->credenciadosConveniosService->getConvenioNamesByCredenciadoId($credenciadoId);

            return response()->json([
                'success' => true,
                'data' => $nomes
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao listar nomes dos convênios do credenciado: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar nomes dos convênios do credenciado.'
            ], 500);
        }
    }

    /**
     * Buscar convênio do credenciado por credenciado e convênio
     *
     * @param int $credenciadoId
     * @param int $convenioId
     * @return JsonResponse
     */
    public function showByCredenciadoAndConvenio(int $credenciadoId, int $convenioId): JsonResponse
    {
        try {
            $credenciadoConvenio = $this->credenciadosConveniosService->getCredenciadoConvenioByCredenciadoAndConvenio($credenciadoId, $convenioId);

            if (!$credenciadoConvenio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Convênio do credenciado não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $credenciadoConvenio
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar convênio do credenciado: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar convênio do credenciado.'
            ], 500);
        }
    }

    /**
     * Criar um novo convênio do credenciado (e sua tabela vinculada)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $credenciadoConvenio = $this->credenciadosConveniosService->createCredenciadoConvenio($request->all());

            Log::info('Convênio do credenciado criado com sucesso', ['id' => $credenciadoConvenio->id]);

            return response()->json([
                'success' => true,
                'message' => 'Convênio do credenciado criado com sucesso.',
                'data' => $credenciadoConvenio
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao criar convênio do credenciado: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar convênio do credenciado.'
            ], 500);
        }
    }

    /**
     * Buscar convênio do credenciado por ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $credenciadoConvenio = $this->credenciadosConveniosService->getCredenciadoConvenioById($id);

            if (!$credenciadoConvenio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Convênio do credenciado não encontrado.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $credenciadoConvenio
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar convênio do credenciado: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar convênio do credenciado.'
            ], 500);
        }
    }

    /**
     * Atualizar convênio do credenciado (e sua tabela vinculada)
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $credenciadoConvenio = $this->credenciadosConveniosService->updateCredenciadoConvenio($id, $request->all());

            Log::info('Convênio do credenciado atualizado com sucesso', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Convênio do credenciado atualizado com sucesso.',
                'data' => $credenciadoConvenio
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar convênio do credenciado: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar convênio do credenciado.'
            ], 500);
        }
    }

    /**
     * Deletar convênio do credenciado (e sua tabela vinculada)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->credenciadosConveniosService->deleteCredenciadoConvenio($id);

            Log::info('Convênio do credenciado deletado com sucesso', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Convênio do credenciado deletado com sucesso.'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar convênio do credenciado: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar convênio do credenciado.'
            ], 500);
        }
    }

    /**
     * Restaurar convênio do credenciado excluído (e sua tabela vinculada)
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $credenciadoConvenio = $this->credenciadosConveniosService->restoreCredenciadoConvenio($id);

            Log::info('Convênio do credenciado restaurado com sucesso', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Convênio do credenciado restaurado com sucesso.',
                'data' => $credenciadoConvenio
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao restaurar convênio do credenciado: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar convênio do credenciado.'
            ], 500);
        }
    }
}
