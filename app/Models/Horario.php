<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'consultorio_id',
        'dia',
        'hora_inicio',
        'hora_fin',
        'duracion_minutos',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class);
    }
}
