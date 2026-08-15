<?php

namespace App\Http\Controllers;

use App\Services\TabelasService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class TabelasController extends Controller
{
    /**
     * @var TabelasService
     */
    protected $tabelasService;

    /**
     * TabelasController constructor.
     *
     * @param TabelasService $tabelasService
     */
    public function __construct(TabelasService $tabelasService)
    {
        $this->tabelasService = $tabelasService;
    }

    /**
     * Listar todas as tabelas
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $tabelas = $this->tabelasService->getAllTabelas();
            
            return response()->json([
                'success' => true,
                'data' => $tabelas
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao listar tabelas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar tabelas.'
            ], 500);
        }
    }

    /**
     * Criar uma nova tabela (método insert)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function insert(Request $request): JsonResponse
    {
        try {
            $tabela = $this->tabelasService->createTabela($request->all());
            
            Log::info('Tabela criada com sucesso', ['tabela_id' => $tabela->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tabela criada com sucesso.',
                'data' => $tabela
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao criar tabela: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar tabela.'
            ], 500);
        }
    }

    /**
     * Buscar tabela por ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $tabela = $this->tabelasService->getTabelaById($id);
            
            if (!$tabela) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tabela não encontrada.'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $tabela
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar tabela: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar tabela.'
            ], 500);
        }
    }

    /**
     * Atualizar tabela
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $tabela = $this->tabelasService->updateTabela($id, $request->all());
            
            Log::info('Tabela atualizada com sucesso', ['tabela_id' => $tabela->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tabela atualizada com sucesso.',
                'data' => $tabela
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar tabela: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar tabela.'
            ], 500);
        }
    }

    /**
     * Deletar tabela
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->tabelasService->deleteTabela($id);
            
            Log::info('Tabela deletada com sucesso', ['tabela_id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tabela deletada com sucesso.'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar tabela: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar tabela.'
            ], 500);
        }
    }

    /**
     * Restaurar tabela deletada
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $tabela = $this->tabelasService->restoreTabela($id);
            
            Log::info('Tabela restaurada com sucesso', ['tabela_id' => $tabela->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tabela restaurada com sucesso.',
                'data' => $tabela
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao restaurar tabela: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao restaurar tabela.'
            ], 500);
        }
    }
}
