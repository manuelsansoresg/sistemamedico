<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'section',
        'model_type',
        'model_id',
        'payload',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function setIpAddressAttribute($value): void
    {
        if ($value) {
            $this->attributes['ip_address'] = $value;

            return;
        }

        $this->attributes['ip_address'] = app()->runningInConsole() ? null : request()->ip();
    }

    public function setUserAgentAttribute($value): void
    {
        $this->attributes['user_agent'] = $value === null ? null : Str::limit((string) $value, 255);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
