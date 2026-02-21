<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurso extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by',
        'nombre',
        'tipo',
        'color',
        'descripcion',
        'activo',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reservas()
    {
        return $this->hasMany(\App\Models\RecursoReserva::class);
    }
}
