<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Credenciados extends Model
{
    //
    use HasFactory, SoftDeletes, BelongsToEmpresa;
    protected $table = 'credenciados';
    protected $primaryKey = 'id';

    protected $fillable = ['empresa_id', 'tipo',
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
     'avatar',
     'credenciado_fat_id',
     'executante_id',
    ];

    public function users()
    {
        return $this->belongsToMany(
            User::class, 
            'users_credenciados', 
            'credenciado_id', 
            'user_id'
        )->withPivot('permissao')
         ->withTimestamps();
    }
}
