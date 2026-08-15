<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Especialidades extends Model
{
    protected $table = 'especialidades';
    protected $fillable = [
        'nome',
        'cbos',        
    ];
}
