<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicos extends Model
{
    use SoftDeletes;

    protected $table = 'medicos';
    protected $fillable = [
        'nome',
        'cpf',
        'especialidade_id',
        'conselho_id',
        'numero_conselho',
        'uf_conselho',
        'telefone',
        'email',
    ];

    public function conselho(): BelongsTo
    {
        return $this->belongsTo(Conselhos::class, 'conselho_id');
    }

    public function especialidade(): BelongsTo
    {
        return $this->belongsTo(Especialidades::class, 'especialidade_id');
    }
}
