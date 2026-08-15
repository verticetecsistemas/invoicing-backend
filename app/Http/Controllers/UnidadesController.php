<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\UnidadesRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UnidadesController extends Controller
{
    public function __construct(protected UnidadesRepositoryInterface $unidadesRepository)
    {
    }

    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->unidadesRepository->all(),
            ]);
        } catch (\Exception $exception) {
            Log::error('Erro ao listar unidades: ' . $exception->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar unidades.',
            ], 500);
        }
    }
}