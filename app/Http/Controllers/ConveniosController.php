<?php

namespace App\Http\Controllers;

use App\Services\ConveniosService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ConveniosController extends Controller
{
    /**
     * @var ConveniosService
     */
    protected $conveniosService;

    /**
     * ConveniosController constructor.
     *
     * @param ConveniosService $conveniosService
     */
    public function __construct(ConveniosService $conveniosService)
    {
        $this->conveniosService = $conveniosService;
    }

    /**
     * Listar todos os convênios
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $convenios = $this->conveniosService->getAllConvenios();
            
            return response()->json([
                'success' => true,
                'data' => $convenios
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao listar convênios: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar convênios.'
            ], 500);
        }
    }

    /**
     * Criar um novo convênio
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $convenio = $this->conveniosService->createConvenio($request->all());
            
            Log::info('Convênio criado com sucesso', ['convenio_id' => $convenio->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Convênio criado com sucesso.',
                'data' => $convenio
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao criar convênio: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar convênio.'
            ], 500);
        }
    }

    /**
     * Buscar convênio por ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $convenio = $this->conveniosService->getConvenioById($id);
            
            if (!$convenio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Convênio não encontrado.'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $convenio
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar convênio: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar convênio.'
            ], 500);
        }
    }

    /**
     * Atualizar convênio
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $convenio = $this->conveniosService->updateConvenio($id, $request->all());
            
            Log::info('Convênio atualizado com sucesso', ['convenio_id' => $convenio->id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Convênio atualizado com sucesso.',
                'data' => $convenio
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar convênio: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar convênio.'
            ], 500);
        }
    }

    /**
     * Deletar convênio
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->conveniosService->deleteConvenio($id);
            
            Log::info('Convênio deletado com sucesso', ['convenio_id' => $id]);
            
            return response()->json([
                'success' => true,
                'message' => 'Convênio deletado com sucesso.'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao deletar convênio: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao deletar convênio.'
            ], 500);
        }
    }
}
