<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstudioArchivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'estudio_id',
        'path',
        'nombre_original',
        'mime_type',
        'size',
    ];

    public function estudio()
    {
        return $this->belongsTo(Estudio::class);
    }
}
