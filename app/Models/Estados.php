<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Estados extends Model
{
    //
    use HasFactory;
    protected $table = 'estados';
    protected $primaryKey = 'estado_id';
    
    protected $fillable = ['codigo_ibge','nome', 'sigla'];

    public function municipios()
    {
        return $this->hasMany(Municipios::class, 'estado_id', 'id');
    }
    
}
