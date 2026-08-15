<?php

namespace App\Http\Controllers;

use App\Services\PacientesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PacientesController extends Controller
{
    public function __construct(protected PacientesService $pacientesService)
    {
    }

    public function index(): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $this->pacientesService->getAllPacientes()]);
        } catch (\Exception $exception) {
            return $this->error($exception, 'listar');
        }
    }

    public function insert(Request $request): JsonResponse
    {
        try {
            $paciente = $this->pacientesService->createPaciente($request->all());

            return response()->json(['success' => true, 'message' => 'Paciente criado com sucesso.', 'data' => $paciente], 201);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'criar');
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $paciente = $this->pacientesService->updatePaciente($id, $request->all());

            return response()->json(['success' => true, 'message' => 'Paciente atualizado com sucesso.', 'data' => $paciente]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'atualizar');
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $this->pacientesService->deletePaciente($id);

            return response()->json(['success' => true, 'message' => 'Paciente deletado com sucesso.']);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'deletar');
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $paciente = $this->pacientesService->restorePaciente($id);

            return response()->json(['success' => true, 'message' => 'Paciente restaurado com sucesso.', 'data' => $paciente]);
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
        Log::error("Erro ao {$operation} paciente: {$exception->getMessage()}");

        return response()->json(['success' => false, 'message' => "Erro ao {$operation} paciente."], 500);
    }
}