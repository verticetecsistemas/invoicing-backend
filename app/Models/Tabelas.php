<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tabelas extends Model
{
    use SoftDeletes;

    protected $table = 'tabelas';
    protected $fillable = [
        'nome',
        'tipo',
        'ans',        
    ];
}
