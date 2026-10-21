<?php

namespace App\Services;

use App\Models\CredenciadosConvenios;
use App\Models\Convenios;
use App\Models\Guias;
use App\Models\GuiasConsulta;
use App\Models\Iaintegration;
use App\Models\IaintegrationDetail;
use App\Models\Medicos;
use App\Models\Pacientes;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class IaintegrationProcessor
{
    private const STATUS_GUIA = 'Pendente';
    private const UF_CONSELHO = 'RJ';

    public function processPending(): array
    {
        $now = now();
        $ids = Iaintegration::query()
            ->where('integrada', 0)
            ->whereBetween('created_at', [$now->copy()->subDays(3), $now])
            ->orderBy('id')
            ->pluck('id');

        IaintegrationDetail::query()
            ->whereIn('iaintegration_id', $ids)
            ->delete();

        $result = [
            'total' => $ids->count(),
            'processed' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($ids as $id) {
            try {
                $processed = DB::transaction(function () use ($id): bool {
                    $integration = Iaintegration::query()
                        ->whereKey($id)
                        ->lockForUpdate()
                        ->first();

                    if (!$integration || $integration->integrada) {
                        return false;
                    }

                    $this->processOne($integration);
                    $integration->integrada = 1;
                    $integration->save();

                    return true;
                });

                if ($processed) {
                    $result['processed']++;
                }
            } catch (Throwable $exception) {
                Log::error('Erro ao efetivar integração de IA.', [
                    'iaintegration_id' => $id,
                    'exception' => $exception,
                ]);

                IaintegrationDetail::create([
                    'iaintegration_id' => $id,
                    'exception_class' => get_class($exception),
                    'message' => $exception->getMessage(),
                ]);

                $result['failed']++;
                $result['errors'][] = [
                    'iaintegration_id' => $id,
                    'message' => $exception instanceof \DomainException
                        ? $exception->getMessage()
                        : 'Falha inesperada ao efetivar os dados.',
                ];
            }
        }

        return $result;
    }

    private function processOne(Iaintegration $integration): void
    {
        $convenio = Convenios::query()
            ->where('ans', $integration->codigo_ans)
            ->first();

        if (!$convenio) {
            throw new \DomainException("Convênio com ANS '{$integration->codigo_ans}' não encontrado.");
        }

        $credenciadoConvenios = CredenciadosConvenios::query()
            ->join('convenios', 'convenios.id', '=', 'credenciados_convenios.convenio_id')
            ->where('convenios.ans', $integration->codigo_ans)
            ->where('credenciados_convenios.codigo_convenio', $integration->codigo_operadora)
            ->select('credenciados_convenios.*')
            ->orderBy('credenciados_convenios.id')
            ->get();

        if ($credenciadoConvenios->isEmpty()) {
            throw new \DomainException(
                "Vínculo não encontrado para o ANS '{$integration->codigo_ans}' e código de operadora '{$integration->codigo_operadora}'."
            );
        }

        if ($credenciadoConvenios->count() > 1) {
            throw new \DomainException(
                "Mais de um vínculo foi encontrado para o ANS '{$integration->codigo_ans}' e código de operadora '{$integration->codigo_operadora}'."
            );
        }

        $credenciadoConvenio = $credenciadoConvenios->first();

        $existingGuide = Guias::query()
            ->where('credenciado_id', $credenciadoConvenio->credenciado_id)
            ->where('convenio_id', $convenio->id)
            ->where('numero', $integration->guia_prestador)
            ->exists();

        if ($existingGuide) {
            throw new \DomainException('guia em duplicidade');
        }

        $numeroConselho = (string) $integration->numero_conselho;
        if (str_starts_with($numeroConselho, '52')) {
            $numeroConselho = substr($numeroConselho, 2);
        }

        $medico = Medicos::query()
            ->where('numero_conselho', $numeroConselho)
            ->where('uf_conselho', self::UF_CONSELHO)
            ->first();

        if (!$medico) {
            throw new \DomainException(
                "Médico com conselho '{$numeroConselho}/".self::UF_CONSELHO."' não encontrado."
            );
        }

        if (!$credenciadoConvenio->codigo_consulta || $credenciadoConvenio->valor_consulta === null) {
            throw new \DomainException('O vínculo de convênio não possui código e valor de consulta cadastrados.');
        }

        if (!$credenciadoConvenio->entrega_consulta) {
            throw new \DomainException('O vínculo de convênio não possui data de entrega de consulta.');
        }

        if (!User::query()->whereKey(1)->exists()) {
            throw new \DomainException('Usuário de sistema com ID 1 não encontrado.');
        }

        if (!$integration->guia_prestador || strlen($integration->guia_prestador) > 20) {
            throw new \DomainException('Número da guia do prestador ausente ou maior que 20 caracteres.');
        }

        if (!$integration->carteira) {
            throw new \DomainException('Número da carteira do beneficiário não informado.');
        }

        $paciente = Pacientes::withoutGlobalScope('empresa')
            ->where('matricula', $integration->carteira)
            ->first();

        if (!$paciente) {
            if (!$integration->nome_beneficiario) {
                throw new \DomainException('Nome do beneficiário não informado para criar o paciente.');
            }

            $paciente = Pacientes::create([
                'empresa_id' => $credenciadoConvenio->credenciado->empresa_id,
                'nome' => $integration->nome_beneficiario,
                'convenio_id' => $convenio->id,
                'matricula' => $integration->carteira,
                'validade_carteira' => $this->normalizeDate($integration->validade_carteira),
            ]);
        }

        $this->assertConsultationFields($integration);
        $dataAtendimento = $this->normalizeDate($integration->data_atendimento);

        if (!$dataAtendimento) {
            throw new \DomainException('Data de atendimento não informada.');
        }

        $guia = Guias::create([
            'tipo' => 'C',
            'credenciado_id' => $credenciadoConvenio->credenciado_id,
            'convenio_id' => $convenio->id,
            'paciente_id' => $paciente->id,
            'usuario_id' => 1,
            'numero' => $integration->guia_prestador,
            'numero_operadora' => $integration->guia_operadora,
            'data' => $dataAtendimento,
            'data_entrega' => $this->normalizeDate($credenciadoConvenio->entrega_consulta),
            'status' => self::STATUS_GUIA,
        ]);

        GuiasConsulta::create([
            'guia_id' => $guia->id,
            'executante_id' => $medico->id,
            'tipo' => $integration->tp_cons_codigo,
            'tabela' => '22',
            'codigo' => $credenciadoConvenio->codigo_consulta,
            'descricao' => 'CONSULTA ELETIVA',
            'quantidade' => 1,
            'valor' => $credenciadoConvenio->valor_consulta,
            'valor_total' => $credenciadoConvenio->valor_consulta,
            'valor_pago' => 0,
            'atendimento_rn' => $integration->atendimento_rn,
            'indicacao_acidente' => $integration->ia_codigo,
            'cobertura_especial' => $integration->cobertura_especial,
            'regime_atendimento' => $integration->regime_atendimento,
            'ocupacional' => $integration->saude_ocupacional,
        ]);
    }

    private function assertConsultationFields(Iaintegration $integration): void
    {
        foreach ([
            'tp_cons_codigo' => 1,
            'atendimento_rn' => 1,
            'ia_codigo' => 1,
            'regime_atendimento' => 2,
        ] as $field => $maxLength) {
            $value = $integration->{$field};

            if ($value === null || $value === '' || strlen((string) $value) > $maxLength) {
                throw new \DomainException("Campo '{$field}' ausente ou incompatível com a guia de consulta.");
            }
        }

        if ($integration->cobertura_especial !== null && strlen($integration->cobertura_especial) > 2) {
            throw new \DomainException("Campo 'cobertura_especial' excede o tamanho máximo de 2 caracteres.");
        }

        if ($integration->saude_ocupacional !== null && strlen($integration->saude_ocupacional) > 2) {
            throw new \DomainException("Campo 'saude_ocupacional' excede o tamanho máximo de 2 caracteres.");
        }
    }

    private function normalizeDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        foreach (['d/m/Y', 'Y-m-d'] as $format) {
            $date = DateTimeImmutable::createFromFormat('!'.$format, $value);
            $errors = DateTimeImmutable::getLastErrors();

            if (
                $date !== false
                && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))
                && $date->format($format) === $value
            ) {
                return $date->format('Y-m-d');
            }
        }

        throw new \DomainException("Data inválida: '{$value}'.");
    }
}
