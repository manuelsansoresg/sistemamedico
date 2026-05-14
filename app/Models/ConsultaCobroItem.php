<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultaCobroItem extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'consulta_cobro_id',
        'tipo',
        'servicio_id',
        'articulo_cobro_id',
        'nombre_snapshot',
        'cantidad',
        'duracion_minutos_snapshot',
        'precio_catalogo',
        'precio_cobrado',
        'subtotal',
        'precio_modificado',
        'modificado_por',
        'motivo_ajuste',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'duracion_minutos_snapshot' => 'integer',
        'precio_catalogo' => 'decimal:2',
        'precio_cobrado' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'precio_modificado' => 'boolean',
    ];

    public function consultaCobro()
    {
        return $this->belongsTo(ConsultaCobro::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function articuloCobro()
    {
        return $this->belongsTo(ArticuloCobro::class);
    }

    public function modificadoPor()
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }

    public function auditSection(): string
    {
        return 'consulta_cobros';
    }
}
