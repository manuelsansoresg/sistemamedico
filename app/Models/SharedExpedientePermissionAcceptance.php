<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharedExpedientePermissionAcceptance extends Model
{
    protected $fillable = [
        'shared_expediente_permission_id',
        'user_id',
        'actor_role',
        'terms_key',
        'terms_hash',
        'accepted_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(SharedExpedientePermission::class, 'shared_expediente_permission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
