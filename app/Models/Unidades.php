<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidades extends Model
{
    use SoftDeletes;

    protected $table = 'unidades';
    protected $fillable = [
        'nome',
        'sigla',
    ];
}
