<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CredenciadosConveniosTabelas extends Model
{
    use SoftDeletes;

    protected $table = 'credenciados_convenios_tabelas';

    protected $fillable = [
        'credenciado_convenio_id',
        'tabela_padrao_id',
        'tabela_medicamentos_id',
        'tabela_materiais_id',
    ];

    public function credenciadoConvenio()
    {
        return $this->belongsTo(CredenciadosConvenios::class, 'credenciado_convenio_id');
    }

    public function tabelaPadrao()
    {
        return $this->belongsTo(Tabelas::class, 'tabela_padrao_id');
    }

    public function tabelaMedicamentos()
    {
        return $this->belongsTo(Tabelas::class, 'tabela_medicamentos_id');
    }

    public function tabelaMateriais()
    {
        return $this->belongsTo(Tabelas::class, 'tabela_materiais_id');
    }
}
