<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecursoReserva extends Model
{
    use Auditable, HasFactory;

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

    public function auditSection(): string
    {
        return 'recursos_agenda';
    }
}
