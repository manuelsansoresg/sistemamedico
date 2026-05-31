<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharedExpedienteAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'shared_expediente_permission_id',
        'patient_id',
        'doctor_id',
        'actor_id',
        'actor_role',
        'action',
        'payload',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(SharedExpedientePermission::class, 'shared_expediente_permission_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
