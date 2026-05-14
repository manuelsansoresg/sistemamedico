<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticuloCobro extends Model
{
    use Auditable, HasFactory;

    protected $table = 'articulos_cobro';

    protected $fillable = [
        'doctor_id',
        'nombre',
        'descripcion',
        'unidad',
        'precio',
        'activo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function auditSection(): string
    {
        return 'articulos_cobro';
    }
}
