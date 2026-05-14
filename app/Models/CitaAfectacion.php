<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CitaAfectacion extends Model
{
    use Auditable, HasFactory;

    protected $table = 'cita_afectaciones';

    protected $fillable = [
        'consulta_cobro_id',
        'cita_origen_id',
        'cita_afectada_id',
        'paciente_afectado_id',
        'paciente_nombre_snapshot',
        'paciente_telefono_snapshot',
        'paciente_email_snapshot',
        'hora_inicio_original',
        'hora_fin_original',
        'estado_original',
        'hora_fin_origen_proyectada',
        'estado',
        'notas',
        'avisado_at',
        'gestionado_por',
        'reagendada_cita_id',
    ];

    protected $casts = [
        'avisado_at' => 'datetime',
    ];

    public function consultaCobro()
    {
        return $this->belongsTo(ConsultaCobro::class);
    }

    public function citaOrigen()
    {
        return $this->belongsTo(Cita::class, 'cita_origen_id');
    }

    public function citaAfectada()
    {
        return $this->belongsTo(Cita::class, 'cita_afectada_id');
    }

    public function pacienteAfectado()
    {
        return $this->belongsTo(User::class, 'paciente_afectado_id');
    }

    public function gestionadoPor()
    {
        return $this->belongsTo(User::class, 'gestionado_por');
    }

    public function reagendadaCita()
    {
        return $this->belongsTo(Cita::class, 'reagendada_cita_id');
    }

    public function auditSection(): string
    {
        return 'cita_afectaciones';
    }
}
