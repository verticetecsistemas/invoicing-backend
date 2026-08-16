<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CredenciadosConvenios extends Model
{
    //
    use SoftDeletes;
    protected $table = 'credenciados_convenios';
    protected $fillable = [
        'credenciado_id',
        'convenio_id',
        'codigo_convenio',
        'ch_consulta',
        'ch_exame',
        'ch_procedimento',
        'valor_consulta',
        'autonumeracaoguia',
        'entrega_consulta',
        'entrega_sadt',
        'entrega_honorario',
    ];

    protected $appends = ['nome_convenio'];

    public function credenciado()
    {
        return $this->belongsTo(Credenciados::class, 'credenciado_id');
    }

    public function convenio()
    {
        return $this->belongsTo(Convenios::class, 'convenio_id');
    }

    public function tabelas()
    {
        return $this->hasOne(CredenciadosConveniosTabelas::class, 'credenciado_convenio_id');
    }

    public function getNomeConvenioAttribute(): ?string
    {
        return $this->convenio->nome_fantasia ?? $this->convenio->razao_social ?? null;
    }
}
