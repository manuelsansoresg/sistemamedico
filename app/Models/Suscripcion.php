<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Suscripcion extends Model
{
    use Auditable, HasFactory;

    protected $table = 'suscripciones';

    protected $fillable = [
        'user_id',
        'paquete_id',
        'catalogo_id',
        'cantidad',
        'tipo',
        'precio',
        'metodo_pago',
        'estatus_pago',
        'fecha_inicio',
        'fecha_fin',
        'comprobante_pago',
        'referencia_pago',
        'token_pago',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'precio' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function (self $suscripcion) {
            if ($suscripcion->estatus_pago === 'pagado') {
                $fechaInicio = $suscripcion->fecha_inicio
                    ? Carbon::parse($suscripcion->fecha_inicio)
                    : now();

                $suscripcion->fecha_inicio = $fechaInicio;
                $suscripcion->fecha_fin = $fechaInicio->copy()->addYear();
            } else {
                $suscripcion->fecha_fin = null;
            }
        });

        static::retrieved(function (self $suscripcion) {
            if ($suscripcion->estatus_pago === 'pagado' && ! $suscripcion->fecha_fin) {
                $fechaInicio = $suscripcion->fecha_inicio
                    ? Carbon::parse($suscripcion->fecha_inicio)
                    : ($suscripcion->created_at ? Carbon::parse($suscripcion->created_at) : now());

                $suscripcion->fecha_inicio = $fechaInicio;
                $suscripcion->fecha_fin = $fechaInicio->copy()->addYear();
                $suscripcion->saveQuietly();
            }
        });
    }

    public function scopePagadaVigente($query)
    {
        $oneYearAgo = now()->subYear();

        return $query->where('estatus_pago', 'pagado')
            ->where(function ($q) use ($oneYearAgo) {
                $q->where(function ($q2) {
                    $q2->whereNotNull('fecha_fin')
                        ->where('fecha_fin', '>=', now());
                })->orWhere(function ($q2) use ($oneYearAgo) {
                    $q2->whereNull('fecha_fin')
                        ->where('created_at', '>=', $oneYearAgo);
                });
            });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paquete()
    {
        return $this->belongsTo(Paquete::class);
    }

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class);
    }

    public function ganancia()
    {
        return $this->hasOne(Ganancia::class);
    }

    public function pacientes()
    {
        return $this->belongsToMany(User::class, 'doctor_patient', 'suscripcion_id', 'patient_id');
    }

    public function auditSection(): string
    {
        return 'suscripciones';
    }
}
