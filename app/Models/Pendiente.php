<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendiente extends Model
{
    protected $fillable = [
        'user_id',
        'recordatorio',
        'fecha',
        'hora',
        'activo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime', // or 'string' depending on needs, but datetime allows formatting
        'activo' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
