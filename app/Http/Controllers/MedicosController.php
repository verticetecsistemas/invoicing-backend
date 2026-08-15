<?php

namespace App\Http\Controllers;

use App\Services\MedicosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MedicosController extends Controller
{
    public function __construct(protected MedicosService $medicosService)
    {
    }

    public function index(): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $this->medicosService->getAllMedicos()]);
        } catch (\Exception $exception) {
            return $this->error($exception, 'listar');
        }
    }

    public function insert(Request $request): JsonResponse
    {
        try {
            $medico = $this->medicosService->createMedico($request->all());

            return response()->json(['success' => true, 'message' => 'Médico criado com sucesso.', 'data' => $medico], 201);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'criar');
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $medico = $this->medicosService->updateMedico($id, $request->all());

            return response()->json(['success' => true, 'message' => 'Médico atualizado com sucesso.', 'data' => $medico]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'atualizar');
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $this->medicosService->deleteMedico($id);

            return response()->json(['success' => true, 'message' => 'Médico deletado com sucesso.']);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'deletar');
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $medico = $this->medicosService->restoreMedico($id);

            return response()->json(['success' => true, 'message' => 'Médico restaurado com sucesso.', 'data' => $medico]);
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
        Log::error("Erro ao {$operation} médico: {$exception->getMessage()}");

        return response()->json(['success' => false, 'message' => "Erro ao {$operation} médico."], 500);
    }
}