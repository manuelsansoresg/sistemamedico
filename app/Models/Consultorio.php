<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultorio extends Model
{
    protected $fillable = [
        'nombre',
        'direccion',
        'lat',
        'lng',
        'telefono',
        'activo',
        'created_by',
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
}
