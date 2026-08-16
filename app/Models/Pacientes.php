<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pacientes extends Model
{
    use SoftDeletes;

    protected $table = 'pacientes';
    protected $fillable = [
        'nome',
        'nome_social',
        'convenio_id',
        'matricula',
        'numero_sus',
        'data_nascimento',
        'titular',
        'observacoes',
        'nome_titular',
    ];

    protected $appends = [
        'convenio_razao_social',
    ];

    public function convenio(): BelongsTo
    {
        return $this->belongsTo(Convenios::class, 'convenio_id');
    }

    public function getConvenioRazaoSocialAttribute(): ?string
    {
        return $this->convenio?->razao_social;
    }
}
