<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'nombre',
        'duracion',
        'costo',
        'created_by',
    ];

    protected $casts = [
        'duracion' => 'integer',
        'costo' => 'decimal:2',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cobroItems()
    {
        return $this->hasMany(ConsultaCobroItem::class);
    }

    public function auditSection(): string
    {
        return 'servicios';
    }
}
