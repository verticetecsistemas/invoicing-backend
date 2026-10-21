<?php

namespace App\Http\Controllers;

use App\Models\Especialidades;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class EspecialidadesController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => Especialidades::orderBy('nome')->get(),
            ]);
        } catch (\Exception $exception) {
            Log::error('Erro ao listar especialidades: ' . $exception->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar especialidades.',
            ], 500);
        }
    }
}