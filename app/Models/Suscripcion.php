<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Suscripcion extends Model
{
    use HasFactory;

    protected $table = 'suscripciones';

    protected $fillable = [
        'user_id',
        'paquete_id',
        'catalogo_id',
        'cantidad',
        'tipo',
        'precio',
        'metodo_pago',
        'estatus_pago',
        'fecha_inicio',
        'fecha_fin',
        'comprobante_pago',
        'referencia_pago',
        'token_pago',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'precio' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paquete()
    {
        return $this->belongsTo(Paquete::class);
    }

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class);
    }

    public function ganancia()
    {
        return $this->hasOne(Ganancia::class);
    }

    public function pacientes()
    {
        return $this->belongsToMany(User::class, 'doctor_patient', 'suscripcion_id', 'patient_id');
    }
}
