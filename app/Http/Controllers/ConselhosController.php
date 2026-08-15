<?php

namespace App\Http\Controllers;

use App\Models\Conselhos;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ConselhosController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => Conselhos::all(),
            ]);
        } catch (\Exception $exception) {
            Log::error('Erro ao listar conselhos: ' . $exception->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar conselhos.',
            ], 500);
        }
    }
}