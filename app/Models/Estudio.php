<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudio extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'consulta_id',
        'orden',
        'observacion',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    public function archivos()
    {
        return $this->hasMany(EstudioArchivo::class);
    }
}
