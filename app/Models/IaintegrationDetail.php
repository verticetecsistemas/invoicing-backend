<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IaintegrationDetail extends Model
{
    protected $table = 'iaintegration_details';

    protected $fillable = [
        'iaintegration_id',
        'exception_class',
        'message',
    ];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Iaintegration::class, 'iaintegration_id');
    }
}
