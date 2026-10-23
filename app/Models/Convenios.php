<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Convenios extends Model
{
    //
    protected $table = 'convenios';
    protected $fillable = [
        'cnpj',
        'razao_social',
        'nome_fantasia',
        'endereco',
        'bairro',
        'cidade',
        'estado',
        'cep',
        'telefone',
        'telefone2',
        'email',
        'observacao',
        'ans',
        'digitos_matricula',
        'retorno_dias',
        'avatar'
    ];
}
