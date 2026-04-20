<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    use Auditable;

    protected $fillable = [
        'user_id',
        'nombre',
        'precio',
        'porcentaje_ganancia',
        'descripcion',
        'activo',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paquetes()
    {
        return $this->belongsToMany(Paquete::class)
            ->withPivot('cantidad_maxima', 'precio')
            ->withTimestamps();
    }
}
