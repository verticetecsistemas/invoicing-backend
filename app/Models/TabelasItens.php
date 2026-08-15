<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TabelasItens extends Model
{
    use SoftDeletes;

    protected $table = 'tabelas_itens';

    protected $fillable = [
        'tabelas_id',
        'codigo',
        'descricao',
        'operacional',
        'honorario',
        'valorsadt',
        'anestesia',
        'pontosato',
        'porteanestesico',
        'auxiliares',
        'incidencia',
        'filme',
        'qtdmax',
        'grupo',
        'portesala',
        'autorizacao',
        'titgrupo',
        'profissional2',
        'pofissional3',
        'auxiliar',
        'porteprof',
        'multporte',
        'grupos_id',
        'unidades_id',
        'pacote',
        'pacotimp',
        'codigoans',
        'porte',
        'equipeimp',
    ];

    public function tabela(): BelongsTo
    {
        return $this->belongsTo(Tabelas::class, 'tabelas_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupos::class, 'grupos_id');
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidades::class, 'unidades_id');
    }
}