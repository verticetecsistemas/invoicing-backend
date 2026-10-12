<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guias extends Model
{
    use HasFactory;
    protected $table = 'guias';
    protected $primaryKey = 'id';
    protected $fillable = [
        'tipo',
        'credenciado_id',
        'convenio_id',
        'paciente_id',
        'usuario_id',
        'numero',
        'numero_operadora',
        'data',
        'data_entrega',
        'observacoes',
        'status',
        'lote'
    ];

    public function credenciado(): BelongsTo
    {
        return $this->belongsTo(Credenciados::class, 'credenciado_id');
    }

    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenios::class, 'convenio_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Pacientes::class, 'paciente_id');
    }

    public function consultas(): HasMany
    {
        return $this->hasMany(GuiasConsulta::class, 'guia_id');
    }
}