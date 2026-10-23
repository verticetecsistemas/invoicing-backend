<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RepasseReportService
{
    private const PERIOD_COLUMNS = [
        'atendimento' => 'g.data',
        'entrega' => 'g.data_entrega',
        'pagamento' => 'gc.data_pagamento',
    ];

    public function generate(array $filters): array
    {
        $baseadoAuxiliar = (bool) $filters['baseado_medico_auxiliar'];
        $ocultarMedico = (bool) $filters['ocultar_medico'];
        $analitico = $filters['tipo_relatorio'] === 'repasse_analitico';

        $rows = $this->sortRows($this->fetchRows($filters, $baseadoAuxiliar), $filters['ordenacao']);

        $data = ['totais' => $this->totals($rows)];

        if ($ocultarMedico) {
            $data['guias'] = $this->buildGuias($rows, $analitico);

            return $data;
        }

        $data['medicos'] = $rows
            ->groupBy('medico_id')
            ->map(fn (Collection $group) => [
                'medico_id' => $group->first()->medico_id,
                'nome' => $group->first()->medico_nome,
                'numero_conselho' => $group->first()->medico_numero_conselho,
                'uf_conselho' => $group->first()->medico_uf_conselho,
                'totais' => $this->totals($group),
                'guias' => $this->buildGuias($group, $analitico),
            ])
            ->sortBy(fn (array $medico) => mb_strtolower((string) $medico['nome']))
            ->values()
            ->all();

        return $data;
    }

    private function fetchRows(array $filters, bool $baseadoAuxiliar): Collection
    {
        $medicoColumn = $baseadoAuxiliar ? 'gc.auxiliar_id' : 'gc.executante_id';

        $query = DB::table('guias_consulta as gc')
            ->join('guias as g', 'g.id', '=', 'gc.guia_id')
            ->join('pacientes as p', 'p.id', '=', 'g.paciente_id')
            ->join('convenios as c', 'c.id', '=', 'g.convenio_id')
            ->leftJoin('medicos as m', 'm.id', '=', $medicoColumn)
            ->where('g.credenciado_id', $filters['credenciado_id'])
            ->whereBetween(self::PERIOD_COLUMNS[$filters['tipo_periodo']], [$filters['data_inicial'], $filters['data_final']])
            ->select([
                'gc.id as item_id',
                'gc.codigo',
                'gc.descricao',
                'gc.quantidade',
                'gc.valor',
                'gc.valor_total',
                'gc.valor_pago',
                'gc.data_pagamento',
                'g.id as guia_id',
                'g.numero',
                'g.numero_operadora',
                'g.data',
                'g.data_entrega',
                'p.nome as paciente_nome',
                'c.razao_social as convenio_nome',
                'm.id as medico_id',
                'm.nome as medico_nome',
                'm.numero_conselho as medico_numero_conselho',
                'm.uf_conselho as medico_uf_conselho',
            ]);

        // No modo auxiliar, itens sem auxiliar não geram repasse.
        if ($baseadoAuxiliar) {
            $query->whereNotNull('gc.auxiliar_id');
        }

        $query
            ->when($filters['convenio_id'] ?? null, fn ($q, $v) => $q->where('g.convenio_id', $v))
            ->when($filters['medico_id'] ?? null, fn ($q, $v) => $q->where('gc.executante_id', $v))
            ->when(
                $baseadoAuxiliar ? ($filters['medico_auxiliar_id'] ?? null) : null,
                fn ($q, $v) => $q->where('gc.auxiliar_id', $v)
            )
            ->when($filters['procedimento_inicial'] ?? null, fn ($q, $v) => $q->where('gc.codigo', '>=', $v))
            ->when($filters['procedimento_final'] ?? null, fn ($q, $v) => $q->where('gc.codigo', '<=', $v))
            ->when($filters['guia'] ?? null, fn ($q, $v) => $q->where(
                fn ($w) => $w->where('g.numero', $v)->orWhere('g.numero_operadora', $v)
            ));

        return $query->get();
    }

    private function sortRows(Collection $rows, string $ordenacao): Collection
    {
        $keys = fn (object $row): array => match ($ordenacao) {
            'data' => [$row->data, $row->numero],
            'paciente' => [$row->paciente_nome, $row->numero],
            'procedimento' => [$row->codigo, $row->numero],
            default => [$row->numero],
        };

        return $rows->sort(function (object $a, object $b) use ($keys) {
            foreach (array_map(null, $keys($a), $keys($b)) as [$left, $right]) {
                $result = strnatcasecmp((string) $left, (string) $right);

                if ($result !== 0) {
                    return $result;
                }
            }

            return [$a->guia_id, $a->item_id] <=> [$b->guia_id, $b->item_id];
        })->values();
    }

    private function buildGuias(Collection $rows, bool $analitico): array
    {
        return $rows
            ->groupBy('guia_id')
            ->map(function (Collection $items) use ($analitico) {
                $first = $items->first();

                $guia = [
                    'guia_id' => $first->guia_id,
                    'numero' => $first->numero,
                    'numero_operadora' => $first->numero_operadora,
                    'data' => $first->data,
                    'data_entrega' => $first->data_entrega,
                    'paciente' => $first->paciente_nome,
                    'convenio' => $first->convenio_nome,
                ];

                if ($analitico) {
                    $guia['procedimentos'] = $items->map(fn (object $item) => [
                        'codigo' => $item->codigo,
                        'descricao' => $item->descricao,
                        'quantidade' => (int) $item->quantidade,
                        'valor' => round((float) $item->valor, 2),
                        'valor_total' => round((float) $item->valor_total, 2),
                        'valor_pago' => round((float) $item->valor_pago, 2),
                        'data_pagamento' => $item->data_pagamento,
                    ])->values()->all();
                }

                return $guia + $this->totals($items, false);
            })
            ->values()
            ->all();
    }

    private function totals(Collection $rows, bool $withCounts = true): array
    {
        $valorTotal = round((float) $rows->sum('valor_total'), 2);
        $valorPago = round((float) $rows->sum('valor_pago'), 2);

        $totals = [
            'quantidade' => (int) $rows->sum('quantidade'),
            'valor_total' => $valorTotal,
            'valor_pago' => $valorPago,
            'saldo' => round($valorTotal - $valorPago, 2),
        ];

        if ($withCounts) {
            $totals = ['guias' => $rows->pluck('guia_id')->unique()->count(), 'procedimentos' => $rows->count()] + $totals;
        }

        return $totals;
    }
}
