<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantillaCampo extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'plantilla_id',
        'nombre',
        'slug',
        'tipo',
        'es_obligatorio',
        'opciones',
        'orden',
    ];

    protected $casts = [
        'es_obligatorio' => 'boolean',
        'opciones' => 'array',
    ];

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class);
    }
}
