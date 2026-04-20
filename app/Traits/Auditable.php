<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

trait Auditable
{
    protected array $auditPendingChanges = [];

    public static function bootAuditable(): void
    {
        static::creating(function (Model $model) {
            $model->auditPendingChanges = [
                'new' => self::auditSanitizeAttributes($model, $model->getAttributes()),
            ];
        });

        static::created(function (Model $model) {
            self::writeAuditLog($model, 'crear', $model->auditPendingChanges ?: [
                'new' => self::auditSanitizeAttributes($model, $model->getAttributes()),
            ]);
            $model->auditPendingChanges = [];
        });

        static::updating(function (Model $model) {
            $dirty = $model->getDirty();
            $dirty = self::auditRemoveExcluded($model, $dirty);

            $old = [];
            $new = [];

            foreach ($dirty as $key => $value) {
                $old[$key] = $model->getOriginal($key);
                $new[$key] = $value;
            }

            $model->auditPendingChanges = [
                'old' => $old,
                'new' => $new,
            ];
        });

        static::updated(function (Model $model) {
            if (! isset($model->auditPendingChanges['new']) || count($model->auditPendingChanges['new']) === 0) {
                $model->auditPendingChanges = [];

                return;
            }

            self::writeAuditLog($model, 'editar', self::auditSanitizePayload($model, $model->auditPendingChanges));
            $model->auditPendingChanges = [];
        });

        static::deleting(function (Model $model) {
            $model->auditPendingChanges = [
                'old' => self::auditUsesSoftDeletes($model)
                    ? self::auditSoftDeleteIdentifiers($model)
                    : self::auditSanitizeAttributes($model, $model->getAttributes()),
            ];
        });

        static::deleted(function (Model $model) {
            $fallback = self::auditUsesSoftDeletes($model)
                ? ['old' => self::auditSoftDeleteIdentifiers($model)]
                : ['old' => self::auditSanitizeAttributes($model, $model->getAttributes())];

            self::writeAuditLog($model, 'borrar', $model->auditPendingChanges ?: $fallback);
            $model->auditPendingChanges = [];
        });
    }

    protected static function writeAuditLog(?Model $model, string $action, ?array $payload = null, ?string $section = null): void
    {
        try {
            $ipAddress = null;
            $userAgent = null;

            if (! app()->runningInConsole()) {
                $ipAddress = request()->ip();
                $userAgent = Str::limit((string) request()->userAgent(), 255);
            }

            $resolvedSection = $section;
            if (! $resolvedSection && $model) {
                if (method_exists($model, 'auditSection')) {
                    $resolvedSection = (string) $model->auditSection();
                } else {
                    $resolvedSection = $model->getTable();
                }
            }

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'section' => AuditLog::normalizeSection($resolvedSection),
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model ? $model->getKey() : null,
                'payload' => $payload,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('AuditLog write failed', [
                'action' => $action,
                'section' => $section,
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model ? $model->getKey() : null,
                'exception' => $e,
            ]);
        }
    }

    protected static function auditRemoveExcluded(Model $model, array $attributes): array
    {
        foreach (self::auditGetExcludedAttributes($model) as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }

    protected static function auditGetExcludedAttributes(Model $model): array
    {
        $defaults = [
            'password',
            'remember_token',
            'last_login_at',
            'updated_at',
            'created_at',
            'deleted_at',
            'email_verified_at',
            'diagnostico',
            'receta',
            'notas_medicas',
            'subjetivo',
            'objetivo',
            'plan',
            'exploracion_fisica',
        ];

        if (method_exists($model, 'auditExcludedAttributes')) {
            $extra = $model->auditExcludedAttributes();
            if (is_array($extra)) {
                return array_values(array_unique(array_merge($defaults, $extra)));
            }
        }

        return $defaults;
    }

    protected static function auditUsesSoftDeletes(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }

    protected static function auditSoftDeleteIdentifiers(Model $model): array
    {
        $attributes = $model->getAttributes();

        $candidateKeys = [
            $model->getKeyName(),
            'uuid',
            'folio',
            'nombre',
            'name',
            'email',
        ];

        $result = [];

        foreach ($candidateKeys as $key) {
            if (array_key_exists($key, $attributes) && $attributes[$key] !== null && $attributes[$key] !== '') {
                $result[$key] = $attributes[$key];
            }
        }

        if (empty($result)) {
            $result[$model->getKeyName()] = $model->getKey();
        }

        return self::auditRemoveExcluded($model, $result);
    }

    protected static function auditSanitizeAttributes(Model $model, array $attributes): array
    {
        return self::auditRemoveExcluded($model, $attributes);
    }

    protected static function auditSanitizePayload(Model $model, array $payload): array
    {
        $old = isset($payload['old']) && is_array($payload['old']) ? self::auditRemoveExcluded($model, $payload['old']) : [];
        $new = isset($payload['new']) && is_array($payload['new']) ? self::auditRemoveExcluded($model, $payload['new']) : [];

        return [
            'old' => $old,
            'new' => $new,
        ];
    }

    public static function auditManual(string $action, ?array $payload = null, ?string $section = null): void
    {
        self::writeAuditLog(null, $action, $payload, $section);
    }

    public function auditAction(string $action, ?array $payload = null, ?string $section = null): void
    {
        self::writeAuditLog($this, $action, $payload, $section);
    }
}
