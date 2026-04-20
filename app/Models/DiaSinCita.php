<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiaSinCita extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'dias_sin_citas';

    protected $fillable = [
        'user_id',
        'motivo',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'todo_el_dia',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'todo_el_dia' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function consultorios()
    {
        return $this->belongsToMany(Consultorio::class, 'consultorio_dia_sin_cita');
    }
}
