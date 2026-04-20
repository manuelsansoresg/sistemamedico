<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Consultorio extends Model
{
    use Auditable;

    protected $fillable = [
        'nombre',
        'direccion',
        'lat',
        'lng',
        'telefono',
        'activo',
        'created_by',
        'origen_suscripcion_id',
        'origen_tipo',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function diasSinCitas()
    {
        return $this->belongsToMany(DiaSinCita::class, 'consultorio_dia_sin_cita');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'consultorio_user');
    }

    public function origenSuscripcion()
    {
        return $this->belongsTo(Suscripcion::class, 'origen_suscripcion_id');
    }
}
