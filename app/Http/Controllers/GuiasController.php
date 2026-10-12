<?php

namespace App\Http\Controllers;

use App\Models\Credenciados;
use App\Models\Guias;
use App\Models\Pacientes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GuiasController extends Controller
{
    private const TABELA_PADRAO = '22';

    private const RELATIONS = ['credenciado', 'convenio', 'paciente', 'consultas'];

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'credenciado_id' => 'required|integer',
                'paciente_id' => 'required|integer',
            ]);

            $guias = $this->query()
                ->with(['credenciado', 'convenio', 'paciente'])
                ->where('credenciado_id', $filters['credenciado_id'])
                ->where('paciente_id', $filters['paciente_id'])
                ->when($request->query('status'), fn ($q, $v) => $q->where('status', $v))
                ->when($request->query('tipo'), fn ($q, $v) => $q->where('tipo', $v))
                ->orderByDesc('id')
                ->get();

            return response()->json(['success' => true, 'data' => $guias]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'listar');
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $guia = $this->query()->with(self::RELATIONS)->find($id);

            if (!$guia) {
                return $this->notFound();
            }

            return response()->json(['success' => true, 'data' => $guia]);
        } catch (\Exception $exception) {
            return $this->error($exception, 'buscar');
        }
    }

    public function consultas(int $id): JsonResponse
    {
        try {
            $guia = $this->query()->find($id);

            if (!$guia) {
                return $this->notFound();
            }

            $guia->load(['consultas' => fn ($q) => $q->orderBy('id')]);

            return response()->json(['success' => true, 'data' => $guia]);
        } catch (\Exception $exception) {
            return $this->error($exception, 'buscar consultas da');
        }
    }

    public function insert(Request $request): JsonResponse
    {
        try {
            $this->normalizeConsultas($request);
            $data = $request->validate($this->guiaRules() + $this->consultaRules('consultas.*.'));
            $this->assertTenantReferences($data);

            $executanteId = Credenciados::find($data['credenciado_id'])?->executante_id;

            $guia = DB::transaction(function () use ($data, $executanteId) {
                $guia = Guias::create(collect($data)->except('consultas')->all() + ['usuario_id' => Auth::id()]);

                foreach ($data['consultas'] ?? [] as $consulta) {
                    $guia->consultas()->create($this->consultaPayload($consulta, null, $executanteId) + ['valor_pago' => 0]);
                }

                return $guia;
            });

            return response()->json([
                'success' => true,
                'message' => 'Guia criada com sucesso.',
                'data' => $guia->load(self::RELATIONS),
            ], 201);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'criar');
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $guia = $this->query()->find($id);

            if (!$guia) {
                return $this->notFound();
            }

            $this->normalizeConsultas($request);
            $data = $request->validate($this->guiaRules(true) + $this->consultaRules('consultas.*.', true));
            $this->assertTenantReferences($data);

            DB::transaction(function () use ($guia, $data) {
                $guia->update(collect($data)->except('consultas')->all());

                if (array_key_exists('consultas', $data)) {
                    $this->syncConsultas($guia, $data['consultas'] ?? []);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Guia atualizada com sucesso.',
                'data' => $guia->refresh()->load(self::RELATIONS),
            ]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'atualizar');
        }
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $guia = $this->query()->find($id);

            if (!$guia) {
                return $this->notFound();
            }

            DB::transaction(function () use ($guia) {
                $guia->consultas()->delete();
                $guia->delete();
            });

            return response()->json(['success' => true, 'message' => 'Guia deletada com sucesso.']);
        } catch (\Exception $exception) {
            return $this->error($exception, 'deletar');
        }
    }

    // As guias não possuem empresa_id; o vínculo com a empresa vem do credenciado (já filtrado por empresa).
    private function query()
    {
        return Guias::query()->whereHas('credenciado');
    }

    private function assertTenantReferences(array $data): void
    {
        if (isset($data['credenciado_id']) && !Credenciados::find($data['credenciado_id'])) {
            throw ValidationException::withMessages(['credenciado_id' => 'Credenciado inválido.']);
        }

        if (isset($data['paciente_id']) && !Pacientes::find($data['paciente_id'])) {
            throw ValidationException::withMessages(['paciente_id' => 'Paciente inválido.']);
        }
    }

    private function syncConsultas(Guias $guia, array $consultas): void
    {
        $keptIds = [];

        foreach ($consultas as $consulta) {
            if (!empty($consulta['id'])) {
                $item = $guia->consultas()->findOrFail($consulta['id']);
                $item->update($this->consultaPayload($consulta, $item) + array_intersect_key($consulta, array_flip(['valor_pago', 'data_pagamento'])));
            } else {
                $item = $guia->consultas()->create(
                    $this->consultaPayload($consulta, null, $guia->credenciado()->first()?->executante_id) + ['valor_pago' => 0]
                );
            }

            $keptIds[] = $item->id;
        }

        $guia->consultas()->whereNotIn('id', $keptIds)->delete();
    }

    // valor_pago e data_pagamento nunca são aceitos aqui; valor_total é calculado quando não informado.
    private function consultaPayload(array $consulta, $current = null, ?int $executanteId = null): array
    {
        $payload = collect($consulta)->except(['id', 'valor_pago', 'data_pagamento'])->all();

        $payload['tabela'] = self::TABELA_PADRAO;

        if (!$current) {
            // Campos que o front não envia: o executante padrão vem do credenciado e os demais recebem valores TISS padrão.
            $payload['executante_id'] = $payload['executante_id'] ?? $executanteId;
            $payload['atendimento_rn'] = $payload['atendimento_rn'] ?? 'N';
            $payload['indicacao_acidente'] = $payload['indicacao_acidente'] ?? '9';
            $payload['regime_atendimento'] = $payload['regime_atendimento'] ?? '01';

            if (empty($payload['executante_id'])) {
                throw ValidationException::withMessages(['consultas' => 'Informe o executante ou cadastre um executante padrão no credenciado.']);
            }
        }

        if ($current) {
            $payload = array_filter($payload, fn ($v, $k) => $v !== null || !in_array($k, ['executante_id', 'atendimento_rn', 'indicacao_acidente', 'regime_atendimento']), ARRAY_FILTER_USE_BOTH);
        }

        $quantidade = $payload['quantidade'] ?? $current?->quantidade;
        $valor = $payload['valor'] ?? $current?->valor;

        if (!isset($payload['valor_total']) && $quantidade !== null && $valor !== null) {
            $payload['valor_total'] = round($quantidade * $valor, 2);
        }

        return $payload;
    }

    private function guiaRules(bool $partial = false): array
    {
        $req = $partial ? 'sometimes|required' : 'required';

        return [
            'tipo' => "$req|string|size:1",
            'credenciado_id' => "$req|integer|exists:credenciados,id",
            'convenio_id' => "$req|integer|exists:convenios,id",
            'paciente_id' => "$req|integer|exists:pacientes,id",
            'numero' => "$req|string|max:20",
            'numero_operadora' => 'nullable|string|max:20',
            'data' => "$req|date",
            'data_entrega' => "$req|date",
            'observacoes' => 'nullable|string',
            'status' => "$req|string|max:20",
            'consultas' => 'sometimes|array',
        ];
    }

    private function consultaRules(string $p, bool $update = false): array
    {
        $rules = [
            'executante_id' => 'nullable|integer|exists:medicos,id',
            'auxiliar_id' => 'nullable|integer|exists:medicos,id',
            'tipo' => 'required|string|size:1',
            'codigo' => 'required|string|max:10',
            'descricao' => 'required|string|max:50',
            'quantidade' => 'required|integer|min:1',
            'valor' => 'required|numeric|min:0',
            'valor_total' => 'nullable|numeric|min:0',
            'atendimento_rn' => 'nullable|string|size:1',
            'indicacao_acidente' => 'nullable|string|size:1',
            'cobertura_especial' => 'nullable|string|max:2',
            'regime_atendimento' => 'nullable|string|max:2',
            'ocupacional' => 'nullable|string|max:2',
        ];

        if ($update) {
            $rules += [
                'id' => 'sometimes|integer',
                'valor_pago' => 'sometimes|numeric|min:0',
                'data_pagamento' => 'sometimes|nullable|date',
            ];
        }

        $out = [];
        foreach ($rules as $field => $rule) {
            $out[$p . $field] = $rule;
        }

        return $out;
    }

    // O front pode enviar os itens como "procedimentos".
    private function normalizeConsultas(Request $request): void
    {
        if ($request->has('procedimentos') && !$request->has('consultas')) {
            $request->merge(['consultas' => $request->input('procedimentos')]);
        }
    }

    private function notFound(): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Guia não encontrada.'], 404);
    }

    private function validationError(ValidationException $exception): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Erro de validação.', 'errors' => $exception->errors()], 422);
    }

    private function error(\Exception $exception, string $operation): JsonResponse
    {
        Log::error("Erro ao {$operation} guia: {$exception->getMessage()}");

        return response()->json(['success' => false, 'message' => "Erro ao {$operation} guia."], 500);
    }
}