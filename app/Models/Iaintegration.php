<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Iaintegration extends Model
{
    protected $table = 'iaintegration';

    protected $fillable = [
        'arquivo',
        'pagina',
        'codigo_ans',
        'guia_prestador',
        'guia_operadora',
        'carteira',
        'nome_beneficiario',
        'validade_carteira',
        'codigo_operadora',
        'nome_contratado',
        'cnes',
        'profissional_executante',
        'conselho_profissional',
        'numero_conselho',
        'atendimento_rn',
        'ia_codigo',
        'ia_descricao',
        'data_atendimento',
        'tp_cons_codigo',
        'tp_cons_descricao',
        'cobertura_especial',
        'regime_atendimento',
        'saude_ocupacional',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(IaintegrationDetail::class, 'iaintegration_id');
    }
}
