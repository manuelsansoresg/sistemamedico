<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinica extends Model
{
    protected $fillable = [
        'nombre',
        'direccion',
        'lat',
        'lng',
        'telefono',
        'logotipo',
        'ubicacion',
        'activo',
        'created_by',
        'origen_suscripcion_id',
        'origen_tipo',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'clinica_user');
    }

    public function origenSuscripcion()
    {
        return $this->belongsTo(Suscripcion::class, 'origen_suscripcion_id');
    }
}
