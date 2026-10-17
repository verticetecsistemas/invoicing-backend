<?php

namespace App\Http\Controllers;

use App\Models\Iaintegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class IaintegrationController extends Controller
{
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
