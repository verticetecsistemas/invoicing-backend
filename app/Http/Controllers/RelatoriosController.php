<?php

namespace App\Http\Controllers;

use App\Models\Credenciados;
use App\Services\RepasseReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RelatoriosController extends Controller
{
    public function __construct(protected RepasseReportService $repasseReportService) {}

    public function repasse(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'credenciado_id' => 'required|integer|exists:credenciados,id',
                'tipo_periodo' => 'required|string|in:atendimento,entrega,pagamento',
                'data_inicial' => 'required|date',
                'data_final' => 'required|date|after_or_equal:data_inicial',
                'convenio_id' => 'nullable|integer|exists:convenios,id',
                'medico_id' => 'nullable|integer|exists:medicos,id',
                'baseado_medico_auxiliar' => 'nullable|boolean',
                'medico_auxiliar_id' => 'nullable|integer|exists:medicos,id',
                'ordenacao' => 'nullable|string|in:guia,data,paciente,procedimento',
                'tipo_relatorio' => 'nullable|string|in:repasse_analitico,repasse_sintetico',
                'ocultar_medico' => 'nullable|boolean',
                'procedimento_inicial' => 'nullable|string|max:10',
                'procedimento_final' => 'nullable|string|max:10',
                'guia' => 'nullable|string|max:20',
            ]);

            $this->assertProcedimentoRange($filters);

            $credenciado = Credenciados::find($filters['credenciado_id']);

            if (! $credenciado) {
                throw ValidationException::withMessages(['credenciado_id' => 'Credenciado inválido.']);
            }

            $filters['baseado_medico_auxiliar'] = $request->boolean('baseado_medico_auxiliar');
            $filters['ocultar_medico'] = $request->boolean('ocultar_medico');
            $filters['ordenacao'] ??= 'guia';
            $filters['tipo_relatorio'] ??= 'repasse_analitico';

            return response()->json([
                'success' => true,
                'data' => [
                    'credenciado' => [
                        'id' => $credenciado->id,
                        'razao_social_nome' => $credenciado->razao_social_nome,
                    ],
                    'tipo_relatorio' => $filters['tipo_relatorio'],
                    'tipo_periodo' => $filters['tipo_periodo'],
                    'data_inicial' => $filters['data_inicial'],
                    'data_final' => $filters['data_final'],
                    'baseado_medico_auxiliar' => $filters['baseado_medico_auxiliar'],
                    'ocultar_medico' => $filters['ocultar_medico'],
                    'ordenacao' => $filters['ordenacao'],
                ] + $this->repasseReportService->generate($filters),
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Exception $exception) {
            Log::error('Erro ao gerar relatório de repasse: '.$exception->getMessage());

            return response()->json(['success' => false, 'message' => 'Erro ao gerar relatório de repasse.'], 500);
        }
    }

    private function assertProcedimentoRange(array $filters): void
    {
        $inicial = $filters['procedimento_inicial'] ?? null;
        $final = $filters['procedimento_final'] ?? null;

        if ($inicial !== null && $final !== null && strnatcmp($inicial, $final) > 0) {
            throw ValidationException::withMessages([
                'procedimento_final' => 'O procedimento final deve ser maior ou igual ao inicial.',
            ]);
        }
    }
}
