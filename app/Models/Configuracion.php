<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $fillable = [
        'user_id',
        'created_by',
        'aceptar_transferencia_bancaria',
        'banco',
        'titular',
        'cuenta',
        'clabe',
        'aceptar_pagos_con_tarjeta',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
