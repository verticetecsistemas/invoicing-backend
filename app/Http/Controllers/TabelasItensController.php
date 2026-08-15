<?php

namespace App\Http\Controllers;

use App\Services\TabelasItensService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TabelasItensController extends Controller
{
    public function __construct(protected TabelasItensService $tabelasItensService)
    {
    }

    public function index(int $tabelaId): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $this->tabelasItensService->getAllTabelasItens($tabelaId)]);
        } catch (\Exception $exception) {
            return $this->error($exception, 'listar');
        }
    }

    public function insert(Request $request): JsonResponse
    {
        try {
            $item = $this->tabelasItensService->createTabelaItem($request->all());

            return response()->json(['success' => true, 'message' => 'Item da tabela criado com sucesso.', 'data' => $item], 201);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'criar');
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $item = $this->tabelasItensService->getTabelaItemById($id);

            return $item
                ? response()->json(['success' => true, 'data' => $item])
                : response()->json(['success' => false, 'message' => 'Item da tabela não encontrado.'], 404);
        } catch (\Exception $exception) {
            return $this->error($exception, 'buscar');
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $item = $this->tabelasItensService->updateTabelaItem($id, $request->all());

            return response()->json(['success' => true, 'message' => 'Item da tabela atualizado com sucesso.', 'data' => $item]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'atualizar');
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->tabelasItensService->deleteTabelaItem($id);

            return response()->json(['success' => true, 'message' => 'Item da tabela deletado com sucesso.']);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'deletar');
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $item = $this->tabelasItensService->restoreTabelaItem($id);

            return response()->json(['success' => true, 'message' => 'Item da tabela restaurado com sucesso.', 'data' => $item]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'restaurar');
        }
    }

    private function validationError(ValidationException $exception): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Erro de validação.', 'errors' => $exception->errors()], 422);
    }

    private function error(\Exception $exception, string $operation): JsonResponse
    {
        Log::error("Erro ao {$operation} item da tabela: {$exception->getMessage()}");

        return response()->json(['success' => false, 'message' => "Erro ao {$operation} item da tabela."], 500);
    }
}