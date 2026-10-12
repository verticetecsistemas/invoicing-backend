<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresas extends Model
{
    //
    use HasFactory;
    protected $table = 'empresas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'tipo',
        'cnpj_cpf',
        'razao_social_nome',
        'nome_fantasia_apelido',
        'inscricao_estadual',
        'inscricao_municipal',
        'cnes',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'telefone',
        'celular',
        'email',
        'observacoes',
        'responsavel',
        'avatar'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    public function credenciados(): HasMany
    {
        return $this->hasMany(Credenciados::class, 'empresa_id');
    }

    public function pacientes(): HasMany
    {
        return $this->hasMany(Pacientes::class, 'empresa_id');
    }

    public function tabelas(): HasMany
    {
        return $this->hasMany(Tabelas::class, 'empresa_id');
    }
}
