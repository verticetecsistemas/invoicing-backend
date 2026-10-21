<?php

namespace App\Http\Controllers;

use App\Models\Iaintegration;
use App\Models\Guias;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class IaintegrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data' => ['sometimes', 'required', 'date_format:Y-m-d'],
        ]);
        $date = $validated['data'] ?? now()->toDateString();

        $integrations = Iaintegration::query()
            ->whereDate('created_at', $date)
            ->with(['details' => fn ($query) => $query->orderByDesc('id')])
            ->orderByDesc('id')
            ->get();

        $mappings = collect();
        $guides = collect();

        if ($integrations->isNotEmpty()) {
            $mappings = DB::table('credenciados_convenios as cc')
                ->join('convenios as cv', 'cv.id', '=', 'cc.convenio_id')
                ->join('credenciados as cr', 'cr.id', '=', 'cc.credenciado_id')
                ->whereNull('cc.deleted_at')
                ->whereNull('cr.deleted_at')
                ->whereIn('cv.ans', $integrations->pluck('codigo_ans')->filter()->unique())
                ->whereIn('cc.codigo_convenio', $integrations->pluck('codigo_operadora')->filter()->unique())
                ->select([
                    'cv.ans',
                    'cc.codigo_convenio',
                    'cc.credenciado_id',
                    'cc.convenio_id',
                    'cr.nome_fantasia_apelido as credenciado',
                    'cr.razao_social_nome as credenciado_razao_social',
                    'cv.nome_fantasia as convenio',
                    'cv.razao_social as convenio_razao_social',
                ])
                ->get()
                ->groupBy(fn (object $mapping) => $mapping->ans.'|'.$mapping->codigo_convenio);

            $guides = Guias::query()
                ->with(['credenciado', 'convenio'])
                ->whereIn('numero', $integrations->pluck('guia_prestador')->filter()->unique())
                ->get()
                ->groupBy('numero');
        }

        $data = $integrations->map(function (Iaintegration $integration) use ($mappings, $guides): array {
            $candidates = $mappings->get($integration->codigo_ans.'|'.$integration->codigo_operadora, collect());
            $guide = $guides->get($integration->guia_prestador, collect())
                ->first(function ($guide) use ($candidates): bool {
                    return $candidates->contains(fn (object $mapping): bool =>
                        (int) $mapping->credenciado_id === (int) $guide->credenciado_id
                        && (int) $mapping->convenio_id === (int) $guide->convenio_id
                    );
                });
            $mapping = $guide
                ? $candidates->first(fn (object $candidate): bool =>
                    (int) $candidate->credenciado_id === (int) $guide->credenciado_id
                    && (int) $candidate->convenio_id === (int) $guide->convenio_id
                )
                : ($candidates->count() === 1 ? $candidates->first() : null);
            $latestDetail = $integration->details->first();

            return [
                'data' => $integration->created_at->toDateString(),
                'credenciado' => $mapping
                    ? ($mapping->credenciado ?: $mapping->credenciado_razao_social)
                    : null,
                'convenio' => $mapping
                    ? ($mapping->convenio ?: $mapping->convenio_razao_social)
                    : null,
                'guia' => $guide?->numero ?? $integration->guia_prestador,
                'status' => $latestDetail
                    ? 'erro'
                    : ($integration->integrada ? 'integrado' : 'pendente'),
                'detalhes' => $latestDetail ? [[
                    'classe' => $latestDetail->exception_class,
                    'mensagem' => $latestDetail->message,
                ]] : [],
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'data_selecionada' => $date,
        ]);
    }

    public function insert(Request $request): JsonResponse
    {
        try {
            $validated = validator(
                ['items' => $request->all()],
                [
                    'items' => 'required|array|min:1',
                    'items.*' => 'required|array',
                    'items.*.arquivo' => 'required|string|max:255',
                    'items.*.pagina' => 'required|integer|min:1',
                    'items.*.campos' => 'required|array',
                    'items.*.campos.registro_ans' => 'required|string|max:255',
                    'items.*.campos.numero_guia_prestador' => 'required|string|max:255',
                    'items.*.campos.numero_guia_operadora' => 'present|nullable|string|max:255',
                    'items.*.campos.numero_carteira' => 'present|nullable|string|max:255',
                    'items.*.campos.validade_carteira' => 'present|nullable|string|max:255',
                    'items.*.campos.atendimento_rn' => 'present|nullable|string|max:1',
                    'items.*.campos.nome_beneficiario' => 'present|nullable|string|max:255',
                    'items.*.campos.codigo_operadora' => 'present|nullable|string|max:255',
                    'items.*.campos.nome_contratado' => 'present|nullable|string|max:255',
                    'items.*.campos.codigo_cnes' => 'present|nullable|string|max:255',
                    'items.*.campos.nome_profissional_executante' => 'present|nullable|string|max:255',
                    'items.*.campos.conselho_profissional' => 'present|nullable|string|max:255',
                    'items.*.campos.numero_conselho' => 'present|nullable|string|max:255',
                    'items.*.campos.indicacao_acidente' => 'required|array',
                    'items.*.campos.indicacao_acidente.codigo' => 'required|string|max:255',
                    'items.*.campos.indicacao_acidente.descricao' => 'required|string|max:255',
                    'items.*.campos.data_atendimento' => 'present|nullable|string|max:255',
                    'items.*.campos.tipo_consulta' => 'required|array',
                    'items.*.campos.tipo_consulta.codigo' => 'required|string|max:255',
                    'items.*.campos.tipo_consulta.descricao' => 'required|string|max:255',
                    'items.*.campos.indicador_cobertura_especial' => 'present|nullable|string|max:255',
                    'items.*.campos.regime_atendimento' => 'present|nullable|string|max:255',
                    'items.*.campos.saude_ocupacional' => 'present|nullable|string|max:255',
                ]
            )->validate();

            $records = DB::transaction(function () use ($validated) {
                return collect($validated['items'])->map(function (array $item) {
                    $fields = $item['campos'];

                    return Iaintegration::create([
                        'arquivo' => $item['arquivo'],
                        'pagina' => $item['pagina'],
                        'codigo_ans' => $fields['registro_ans'],
                        'guia_prestador' => $fields['numero_guia_prestador'],
                        'guia_operadora' => $fields['numero_guia_operadora'],
                        'carteira' => $fields['numero_carteira'],
                        'validade_carteira' => $fields['validade_carteira'],
                        'atendimento_rn' => $fields['atendimento_rn'],
                        'nome_beneficiario' => $fields['nome_beneficiario'],
                        'codigo_operadora' => $fields['codigo_operadora'],
                        'nome_contratado' => $fields['nome_contratado'],
                        'cnes' => $fields['codigo_cnes'],
                        'profissional_executante' => $fields['nome_profissional_executante'],
                        'conselho_profissional' => $fields['conselho_profissional'],
                        'numero_conselho' => $fields['numero_conselho'],
                        'ia_codigo' => $fields['indicacao_acidente']['codigo'],
                        'ia_descricao' => $fields['indicacao_acidente']['descricao'],
                        'data_atendimento' => $fields['data_atendimento'],
                        'tp_cons_codigo' => $fields['tipo_consulta']['codigo'],
                        'tp_cons_descricao' => $fields['tipo_consulta']['descricao'],
                        'cobertura_especial' => $fields['indicador_cobertura_especial'],
                        'regime_atendimento' => $fields['regime_atendimento'],
                        'saude_ocupacional' => $fields['saude_ocupacional'],
                    ]);
                });
            });

            return response()->json([
                'success' => true,
                'message' => 'Dados de integração salvos com sucesso.',
                'data' => $records,
            ], 201);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Exception $exception) {
            Log::error('Erro ao salvar integração de IA: '.$exception->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar dados de integração.',
            ], 500);
        }
    }
}
