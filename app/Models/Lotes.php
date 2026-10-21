<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lotes extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'lotes';
    protected $appends = ['nome_convenio'];

    protected $fillable = [
        'credenciado_id',
        'convenio_id',
        'numero',
        'protocolo',
        'tipo',
        'data',
        'data_entrega',
        'descricao',
        'total',
        'pago',
        'data_pagamento',
        'status',
    ];

    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenios::class, 'convenio_id');
    }

    public function credenciado(): BelongsTo
    {
        return $this->belongsTo(Credenciados::class, 'credenciado_id')->withTrashed();
    }

    public function getNomeConvenioAttribute(): ?string
    {
        return $this->convenio?->nome_fantasia ?? $this->convenio?->razao_social;
    }

    public function guias(): HasMany
    {
        return $this->hasMany(Guias::class, 'lote', 'numero');
    }
}
