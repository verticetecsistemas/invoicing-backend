<?php

namespace App\Http\Controllers;

use App\Models\Guias;
use App\Models\CredenciadosConvenios;
use App\Models\Estados;
use App\Models\Lotes;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LotesController extends Controller
{
    public function index(Request $request, int $credenciado_id): JsonResponse
    {
        try {
            $request->merge(['credenciado_id' => $credenciado_id]);
            $filters = $request->validate([
                'credenciado_id' => 'required|integer|exists:credenciados,id',
            ]);

            $lotes = Lotes::query()
                ->where('credenciado_id', $filters['credenciado_id'])
                ->withCount(['guias as guias'])
                ->with('convenio:id,nome_fantasia,razao_social')
                ->orderByDesc('id')
                ->get();

            return response()->json(['success' => true, 'data' => $lotes]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (\Exception $exception) {
            return $this->error($exception, 'listar');
        }
    }

    public function proximoNumero(): JsonResponse
    {
        try {
            $maiorNumero = Lotes::withTrashed()
                ->pluck('numero')
                ->filter(fn ($numero) => is_numeric($numero))
                ->map(fn ($numero) => (int) $numero)
                ->max() ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'numero' => $maiorNumero + 1,
                ],
            ]);
        } catch (\Exception $exception) {
            return $this->error($exception, 'buscar próximo número do');
        }
    }

    public function xmlConsulta(int $id): Response|JsonResponse
    {
        try {
            $lote = Lotes::query()->with(['convenio', 'credenciado'])->findOrFail($id);

            if ($lote->tipo !== 'C') {
                throw ValidationException::withMessages([
                    'tipo' => ['A geração de XML de consulta só está disponível para lotes do tipo C.'],
                ]);
            }

            $prestadorConvenio = CredenciadosConvenios::query()
                ->where('credenciado_id', $lote->credenciado_id)
                ->where('convenio_id', $lote->convenio_id)
                ->first();

            $registroAns = $this->requireXmlValue($lote->convenio?->ans, 'convenio.ans');
            $codigoPrestador = $this->requireXmlValue($prestadorConvenio?->codigo_convenio, 'codigo_convenio');
            $cnes = $this->requireXmlValue(
                $lote->credenciado?->cnes,
                'credenciado.cnes'
            );

            $guias = Guias::query()
                ->with([
                    'paciente',
                    'consultas.executante.conselho',
                    'consultas.executante.especialidade',
                ])
                ->where('lote', $lote->numero)
                ->where('credenciado_id', $lote->credenciado_id)
                ->where('convenio_id', $lote->convenio_id)
                ->whereDate('data_entrega', $lote->data_entrega)
                ->orderBy('id')
                ->get();

            if ($guias->isEmpty()) {
                throw ValidationException::withMessages([
                    'guias' => ['Não há guias vinculadas a este lote para exportar.'],
                ]);
            }

            foreach ($guias as $guia) {
                if ($guia->tipo !== 'C') {
                    throw ValidationException::withMessages([
                        'guias' => ["A guia {$guia->numero} não é do tipo consulta."],
                    ]);
                }

                if ($guia->consultas->count() !== 1) {
                    throw ValidationException::withMessages([
                        'guias' => ["A guia {$guia->numero} deve possuir exatamente um detalhe de consulta."],
                    ]);
                }
            }

            $document = new \DOMDocument('1.0', 'ISO-8859-1');
            $document->formatOutput = false;
            $namespace = 'http://www.ans.gov.br/padroes/tiss/schemas';
            $root = $document->createElementNS($namespace, 'ans:mensagemTISS');
            $document->appendChild($root);
            $root->setAttributeNS(
                'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:schemaLocation',
                "{$namespace} {$namespace}/tissV4_03_00.xsd"
            );

            $hashValues = [];
            $add = function (\DOMElement $parent, string $tag, ?string $value = null) use (
                $document,
                $namespace,
                &$hashValues
            ): \DOMElement {
                $element = $document->createElementNS($namespace, "ans:{$tag}");
                $parent->appendChild($element);

                if ($value !== null) {
                    $element->appendChild($document->createTextNode($value));
                    $hashValues[] = $value;
                }

                return $element;
            };

            $cabecalho = $add($root, 'cabecalho');
            $identificacaoTransacao = $add($cabecalho, 'identificacaoTransacao');
            $add($identificacaoTransacao, 'tipoTransacao', 'ENVIO_LOTE_GUIAS');
            $add($identificacaoTransacao, 'sequencialTransacao', (string) $lote->numero);
            $add($identificacaoTransacao, 'dataRegistroTransacao', substr((string) $lote->data, 0, 10));
            $add(
                $identificacaoTransacao,
                'horaRegistroTransacao',
                $this->requireXmlValue($lote->created_at?->format('H:i:s'), 'lote.created_at')
            );

            $origem = $add($cabecalho, 'origem');
            $identificacaoPrestador = $add($origem, 'identificacaoPrestador');
            $add($identificacaoPrestador, 'codigoPrestadorNaOperadora', $codigoPrestador);
            $destino = $add($cabecalho, 'destino');
            $add($destino, 'registroANS', $registroAns);
            $add($cabecalho, 'Padrao', '4.03.00');
            $loginSenhaPrestador = $add($cabecalho, 'loginSenhaPrestador');
            $add($loginSenhaPrestador, 'loginPrestador', 'X');
            $add($loginSenhaPrestador, 'senhaPrestador', 'X');

            $prestadorParaOperadora = $add($root, 'prestadorParaOperadora');
            $loteGuias = $add($prestadorParaOperadora, 'loteGuias');
            $add($loteGuias, 'numeroLote', (string) $lote->numero);
            $guiasTiss = $add($loteGuias, 'guiasTISS');

            foreach ($guias as $guia) {
                $consulta = $guia->consultas->first();
                $executante = $consulta->executante;
                $paciente = $guia->paciente;

                $numeroCarteira = $this->requireXmlValue($paciente?->matricula, "guia.{$guia->numero}.paciente.matricula");
                $nomeProfissional = $this->requireXmlValue($executante?->nome, "guia.{$guia->numero}.executante.nome");
                $numeroConselho = $this->requireXmlValue(
                    $executante?->numero_conselho,
                    "guia.{$guia->numero}.executante.numero_conselho"
                );
                $codigoConselho = $this->requireXmlValue(
                    $executante?->conselho?->codigo,
                    "guia.{$guia->numero}.executante.conselho.codigo"
                );

                $ufSigla = strtoupper($this->requireXmlValue(
                    $executante?->uf_conselho,
                    "guia.{$guia->numero}.executante.uf_conselho"
                ));
                $codigoIbgeUf = Estados::query()->where('sigla', $ufSigla)->value('codigo_ibge');
                $ufTiss = $codigoIbgeUf ? substr((string) $codigoIbgeUf, 0, 2) : null;
                $cbos = $this->requireXmlValue(
                    $executante?->especialidade?->cbos,
                    "guia.{$guia->numero}.executante.especialidade.cbos"
                );

                if ($ufTiss === null) {
                    throw ValidationException::withMessages([
                        'uf_conselho' => ["A UF {$ufSigla} não foi encontrada na tabela de estados."],
                    ]);
                }

                $guiaConsulta = $add($guiasTiss, 'guiaConsulta');
                $cabecalhoConsulta = $add($guiaConsulta, 'cabecalhoConsulta');
                $add($cabecalhoConsulta, 'registroANS', (string) $lote->convenio->ans);
                $add($cabecalhoConsulta, 'numeroGuiaPrestador', (string) $guia->numero);

                $dadosBeneficiario = $add($guiaConsulta, 'dadosBeneficiario');
                $add($dadosBeneficiario, 'numeroCarteira', $numeroCarteira);
                $add($dadosBeneficiario, 'atendimentoRN', $this->requireXmlValue(
                    $consulta->atendimento_rn,
                    "guia.{$guia->numero}.consulta.atendimento_rn"
                ));

                $contratadoExecutante = $add($guiaConsulta, 'contratadoExecutante');
                $add($contratadoExecutante, 'codigoPrestadorNaOperadora', $codigoPrestador);
                $add($contratadoExecutante, 'CNES', $cnes);

                $profissionalExecutante = $add($guiaConsulta, 'profissionalExecutante');
                $add($profissionalExecutante, 'nomeProfissional', $nomeProfissional);
                $add($profissionalExecutante, 'conselhoProfissional', $codigoConselho);
                $add($profissionalExecutante, 'numeroConselhoProfissional', $numeroConselho);
                $add($profissionalExecutante, 'UF', $ufTiss);
                $add($profissionalExecutante, 'CBOS', $cbos);

                $add($guiaConsulta, 'indicacaoAcidente', $this->requireXmlValue(
                    $consulta->indicacao_acidente,
                    "guia.{$guia->numero}.consulta.indicacao_acidente"
                ));
                $dadosAtendimento = $add($guiaConsulta, 'dadosAtendimento');
                $add($dadosAtendimento, 'regimeAtendimento', $this->requireXmlValue(
                    $consulta->regime_atendimento,
                    "guia.{$guia->numero}.consulta.regime_atendimento"
                ));
                $add($dadosAtendimento, 'dataAtendimento', substr((string) $guia->data, 0, 10));
                $add($dadosAtendimento, 'tipoConsulta', $this->requireXmlValue(
                    $consulta->tipo,
                    "guia.{$guia->numero}.consulta.tipo"
                ));

                $procedimento = $add($dadosAtendimento, 'procedimento');
                $add($procedimento, 'codigoTabela', (string) $consulta->tabela);
                $add($procedimento, 'codigoProcedimento', (string) $consulta->codigo);
                $add($procedimento, 'valorProcedimento', number_format((float) $consulta->valor, 2, '.', ''));
            }

            $hashInput = mb_convert_encoding(implode('', $hashValues), 'ISO-8859-1', 'UTF-8');
            $epilogo = $add($root, 'epilogo');
            $add($epilogo, 'hash', md5($hashInput));

            return response($document->saveXML(), 200, [
                'Content-Type' => 'application/xml; charset=ISO-8859-1',
                'Content-Disposition' => "attachment; filename=\"lote-{$lote->numero}-consulta.xml\"",
            ]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (ModelNotFoundException $exception) {
            return $this->notFound();
        } catch (\Exception $exception) {
            return $this->error($exception, 'gerar XML de consulta do');
        }
    }

    public function validar(int $id, string $versao): JsonResponse
    {
        if (!preg_match('/^\d{5}$/', $versao)) {
            return response()->json([
                'success' => false,
                'message' => 'Versão de schema inválida. Informe cinco dígitos, por exemplo 40300.',
            ], 422);
        }

        $schemaVersion = substr($versao, 0, 1)
            . '_'
            . substr($versao, 1, 2)
            . '_'
            . substr($versao, 3, 2);
        $schemaPath = storage_path("schemas/{$versao}/tissV{$schemaVersion}.xsd");

        if (!is_file($schemaPath) || !is_readable($schemaPath)) {
            return response()->json([
                'success' => false,
                'message' => "Schema TISS da versão {$versao} não encontrado.",
            ], 404);
        }

        $xmlResponse = $this->xmlConsulta($id);
        if ($xmlResponse instanceof JsonResponse) {
            return $xmlResponse;
        }

        $document = new \DOMDocument();
        $previousLibxmlSetting = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $loaded = $document->loadXML($xmlResponse->getContent(), LIBXML_NONET);
            $valid = $loaded && $document->schemaValidate($schemaPath, LIBXML_NONET);
            $errors = array_map(
                fn (\LibXMLError $error) => [
                    'linha' => $error->line,
                    'coluna' => $error->column,
                    'mensagem' => trim($error->message),
                ],
                libxml_get_errors()
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlSetting);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'lote_id' => $id,
                'versao' => $versao,
                'valido' => $valid,
                'erros' => $errors,
            ],
        ]);
    }

    public function insert(Request $request): JsonResponse
    {
        try {
            $lote = Lotes::create($request->validate($this->rules()));

            return response()->json([
                'success' => true,
                'message' => 'Lote criado com sucesso.',
                'data' => $lote,
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
            $lote = Lotes::query()->findOrFail($id);
            $lote->update($request->validate($this->rules(true)));

            return response()->json([
                'success' => true,
                'message' => 'Lote atualizado com sucesso.',
                'data' => $lote->refresh(),
            ]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (ModelNotFoundException $exception) {
            return $this->notFound();
        } catch (\Exception $exception) {
            return $this->error($exception, 'atualizar');
        }
    }

    public function processar(int $id): JsonResponse
    {
        try {
            $lote = Lotes::query()->findOrFail($id);

            $resultado = DB::transaction(function () use ($lote) {
                $guiasAtualizadas = Guias::query()
                    ->where('credenciado_id', $lote->credenciado_id)
                    ->where('convenio_id', $lote->convenio_id)
                    ->whereDate('data_entrega', $lote->data_entrega)
                    ->update(['lote' => $lote->numero]);

                if ($lote->tipo === 'C') {
                    $lote->total = DB::table('guias_consulta')
                        ->join('guias', 'guias.id', '=', 'guias_consulta.guia_id')
                        ->where('guias.lote', $lote->numero)
                        ->where('guias.credenciado_id', $lote->credenciado_id)
                        ->where('guias.convenio_id', $lote->convenio_id)
                        ->whereDate('guias.data_entrega', $lote->data_entrega)
                        ->sum('guias_consulta.valor_total');
                    $lote->save();
                }

                return $guiasAtualizadas;
            });

            $data = [
                'lote_id' => $lote->id,
                'numero' => $lote->numero,
                'guias_atualizadas' => $resultado,
            ];

            if ($lote->tipo === 'C') {
                $data['total'] = $lote->total;
            }

            return response()->json([
                'success' => true,
                'message' => 'Lote processado com sucesso.',
                'data' => $data,
            ]);
        } catch (ModelNotFoundException $exception) {
            return $this->notFound();
        } catch (\Exception $exception) {
            return $this->error($exception, 'processar');
        }
    }

    public function restore(int $id): JsonResponse
    {
        try {
            $lote = Lotes::onlyTrashed()->findOrFail($id);
            $lote->restore();

            return response()->json([
                'success' => true,
                'message' => 'Lote restaurado com sucesso.',
                'data' => $lote->refresh(),
            ]);
        } catch (ModelNotFoundException $exception) {
            return $this->notFound();
        } catch (\Exception $exception) {
            return $this->error($exception, 'restaurar');
        }
    }

    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'credenciado_id' => [$required, 'integer', 'exists:credenciados,id'],
            'convenio_id' => [$required, 'integer', 'exists:convenios,id'],
            'numero' => [$required, 'string', 'max:255'],
            'protocolo' => ['nullable', 'string', 'max:255'],
            'tipo' => [$required, 'string', 'size:1'],
            'data' => [$required, 'date'],
            'data_entrega' => [$required, 'date'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'total' => ['nullable', 'numeric', 'max:99999999.99'],
            'pago' => ['nullable', 'numeric', 'max:99999999.99'],
            'data_pagamento' => ['nullable', 'date'],
            'status' => ['sometimes', 'string', 'max:255'],
        ];
    }

    private function validationError(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Erro de validação.',
            'errors' => $exception->errors(),
        ], 422);
    }

    private function requireXmlValue(mixed $value, string $field): string
    {
        if ($value === null || trim((string) $value) === '') {
            throw ValidationException::withMessages([
                $field => ['Campo obrigatório para gerar o XML de consulta.'],
            ]);
        }

        return (string) $value;
    }

    private function error(\Exception $exception, string $operation): JsonResponse
    {
        Log::error("Erro ao {$operation} lote: {$exception->getMessage()}");

        return response()->json([
            'success' => false,
            'message' => "Erro ao {$operation} lote.",
        ], 500);
    }

    private function notFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Lote não encontrado.',
        ], 404);
    }
}
