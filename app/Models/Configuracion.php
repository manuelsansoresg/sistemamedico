<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use Auditable;

    protected $fillable = [
        'user_id',
        'created_by',
        'aceptar_transferencia_bancaria',
        'banco',
        'titular',
        'cuenta',
        'clabe',
        'aceptar_pagos_con_tarjeta',
        'branding_logo_path',
    ];

    public function auditExcludedAttributes(): array
    {
        return [
            'cuenta',
            'clabe',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
