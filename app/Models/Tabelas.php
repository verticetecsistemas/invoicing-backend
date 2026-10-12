<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEmpresa;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tabelas extends Model
{
    use SoftDeletes, BelongsToEmpresa;

    protected $table = 'tabelas';
    protected $fillable = [
        'empresa_id',
        'nome',
        'tipo',
        'ans',        
    ];
}
