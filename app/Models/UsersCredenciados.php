<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class UsersCredenciados extends Model
{
    //
    use HasFactory;
    protected $table = 'users_credenciados';
    protected $primaryKey = 'id';

    protected $fillable = [
     'user_id',
     'credenciado_id',
     'permissao'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function credenciado()
    {
        return $this->belongsTo(Credenciados::class, 'credenciado_id');
    }
}
