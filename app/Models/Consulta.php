<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consulta extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'cita_id',
        'doctor_id',
        'paciente_id',
        'plantilla_id',
        'peso',
        'estatura',
        'alergias',
        'diagnostico',
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

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class);
    }

    public function valores()
    {
        return $this->hasMany(ConsultaValor::class);
    }

    public function estudios()
    {
        return $this->hasMany(Estudio::class);
    }
}
