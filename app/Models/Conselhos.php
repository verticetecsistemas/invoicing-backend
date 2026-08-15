<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conselhos extends Model
{
    protected $table = 'conselhos';
    protected $fillable = [
        'nome',
        'sigla',
    ];
}
