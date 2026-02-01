<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultaValor extends Model
{
    use HasFactory;

    protected $table = 'consulta_valores';

    protected $fillable = [
        'consulta_id',
        'plantilla_campo_id',
        'valor',
    ];

    public function consulta()
    {
        return $this->belongsTo(Consulta::class);
    }

    public function campo()
    {
        return $this->belongsTo(PlantillaCampo::class, 'plantilla_campo_id');
    }
}
