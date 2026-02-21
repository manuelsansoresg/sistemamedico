<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecursoReserva extends Model
{
    use HasFactory;

    protected $fillable = [
        'recurso_id',
        'user_id',
        'titulo',
        'comentario',
        'inicio',
        'fin',
        'estado',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
    ];

    public function recurso()
    {
        return $this->belongsTo(Recurso::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

