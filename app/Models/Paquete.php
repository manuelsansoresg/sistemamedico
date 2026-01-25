<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paquete extends Model
{
    protected $fillable = [
        'user_id',
        'nombre',
        'precio',
        'activo',
        'tipo',
        'validar_cedula',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function catalogos()
    {
        return $this->belongsToMany(Catalogo::class)
            ->withPivot('cantidad_maxima', 'precio')
            ->withTimestamps();
    }
}
