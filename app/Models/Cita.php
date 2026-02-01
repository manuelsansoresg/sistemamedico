<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'paciente_id',
        'consultorio_id',
        'clinica_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'motivo',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'datetime', // or 'immutable_time' if using carbon
        'hora_fin' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function paciente()
    {
        return $this->belongsTo(User::class, 'paciente_id');
    }

    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class);
    }

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }
}
