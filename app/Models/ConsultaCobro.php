<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultaCobro extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'cita_id',
        'doctor_id',
        'paciente_id',
        'estado_instrucciones',
        'instrucciones_cobro',
        'hora_fin_original',
        'hora_fin_proyectada',
        'duracion_extra_minutos',
        'subtotal_servicios',
        'subtotal_articulos',
        'total',
        'estado_cobro',
        'enviado_por',
        'enviado_at',
    ];

    protected $casts = [
        'duracion_extra_minutos' => 'integer',
        'subtotal_servicios' => 'decimal:2',
        'subtotal_articulos' => 'decimal:2',
        'total' => 'decimal:2',
        'enviado_at' => 'datetime',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function paciente()
    {
        return $this->belongsTo(User::class, 'paciente_id');
    }

    public function enviadoPor()
    {
        return $this->belongsTo(User::class, 'enviado_por');
    }

    public function items()
    {
        return $this->hasMany(ConsultaCobroItem::class);
    }

    public function afectaciones()
    {
        return $this->hasMany(CitaAfectacion::class);
    }

    public function ganancia()
    {
        return $this->hasOne(Ganancia::class);
    }

    public function auditSection(): string
    {
        return 'consulta_cobros';
    }
}
