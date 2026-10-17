<?php

namespace App\Http\Controllers;

use App\Services\IaintegrationProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class IaintegrationProcessingController extends Controller
{
    public function process(IaintegrationProcessor $processor): JsonResponse
    {
        try {
            $result = $processor->processPending();

            return response()->json([
                'success' => $result['failed'] === 0,
                'message' => 'Processamento de integrações concluído.',
                'data' => $result,
            ], $result['failed'] > 0 ? 207 : 200);
        } catch (Throwable $exception) {
            Log::error('Falha ao iniciar o processamento das integrações de IA.', [
                'exception' => $exception,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível processar as integrações de IA.',
            ], 500);
        }
    }
}
