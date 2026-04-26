<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ganancia extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'user_id',
        'suscripcion_id',
        'catalogo_id',
        'paquete_id',
        'monto_total',
        'monto_ganancia_doctor',
        'porcentaje_aplicado',
        'concepto',
        'tipo_ingreso',
        'fecha',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_ganancia_doctor' => 'decimal:2',
        'porcentaje_aplicado' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function suscripcion()
    {
        return $this->belongsTo(Suscripcion::class);
    }

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class);
    }

    public function paquete()
    {
        return $this->belongsTo(Paquete::class);
    }
}
