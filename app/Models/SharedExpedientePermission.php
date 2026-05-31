<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SharedExpedientePermission extends Model
{
    public const TYPE_READ = 'read';

    public const TYPE_DOWNLOAD = 'download';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'especialidad_id',
        'permission_type',
        'can_edit_owned_records',
        'status',
        'doctor_search_text',
        'external_doctor_name',
        'external_doctor_email',
        'temporary_access_code',
        'starts_at',
        'expires_at',
        'revoked_at',
        'patient_terms_accepted_at',
        'patient_terms_hash',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'patient_terms_accepted_at' => 'datetime',
            'can_edit_owned_records' => 'boolean',
            'temporary_access_code' => 'encrypted',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(SharedExpedientePermissionAcceptance::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(SharedExpedienteAuditLog::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_ACTIVE)
            ->where('expires_at', '>', now())
            ->whereNull('revoked_at');
    }

    public function allowsDownload(): bool
    {
        return $this->permission_type === self::TYPE_DOWNLOAD;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->revoked_at === null
            && $this->expires_at->isFuture();
    }

    public function matchesTemporaryAccessCode(?string $code): bool
    {
        return $this->isActive()
            && $this->doctor_id === null
            && $this->temporary_access_code !== null
            && hash_equals($this->temporary_access_code, trim((string) $code));
    }
}
