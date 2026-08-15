<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grupos extends Model
{
    use SoftDeletes;

    protected $table = 'grupos';
    protected $fillable = [
        'nome',
        'outras',
        'ordem',
    ];
}
