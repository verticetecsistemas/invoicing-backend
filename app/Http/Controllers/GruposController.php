<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\GruposRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class GruposController extends Controller
{
    public function __construct(protected GruposRepositoryInterface $gruposRepository)
    {
    }

    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->gruposRepository->all(),
            ]);
        } catch (\Exception $exception) {
            Log::error('Erro ao listar grupos: ' . $exception->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar grupos.',
            ], 500);
        }
    }
}