<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuiasConsulta extends Model
{
    use HasFactory;

    protected $table = 'guias_consulta';

    protected $fillable = [
        'guia_id',
        'executante_id',
        'auxiliar_id',
        'tipo',
        'tabela',
        'codigo',
        'descricao',
        'quantidade',
        'valor',
        'valor_total',
        'valor_pago',
        'data_pagamento',
        'atendimento_rn',
        'indicacao_acidente',
        'cobertura_especial',
        'regime_atendimento',
        'ocupacional',
    ];

    public function guia(): BelongsTo
    {
        return $this->belongsTo(Guias::class, 'guia_id');
    }

    public function executante(): BelongsTo
    {
        return $this->belongsTo(Medicos::class, 'executante_id');
    }
}
