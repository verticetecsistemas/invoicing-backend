<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GrauParticipacao extends Model
{
    use HasFactory;

    protected $table = 'grau_participacao';

    protected $fillable = [
        'codigo',
        'descricao',
        'ativo',
    ];

}
