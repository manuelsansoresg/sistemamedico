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

    public static function sectionLabels(): array
    {
        return [
            'configuracions' => 'configuraciones',
            'configuracion' => 'configuraciones',
            'users' => 'usuarios',
            'user' => 'usuarios',
            'patients' => 'pacientes',
            'patient' => 'pacientes',
            'resources' => 'recursos',
            'resource' => 'recursos',
            'security' => 'seguridad',
            'auth' => 'seguridad',
            'ia' => 'inteligencia_artificial',
            'ai' => 'inteligencia_artificial',
        ];
    }

    public static function normalizeSection(?string $section): ?string
    {
        if ($section === null) {
            return null;
        }

        $normalized = Str::of($section)->trim()->lower()->toString();

        return self::sectionLabels()[$normalized] ?? $normalized;
    }

    public static function sectionLabel(?string $section): string
    {
        $normalized = self::normalizeSection($section);

        return $normalized ?: '-';
    }

    public function getSectionLabelAttribute(): string
    {
        return self::sectionLabel($this->section);
    }

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

    // -------------------------------------------------------------------
    // Category routing helpers
    // -------------------------------------------------------------------

    public static function categories(): array
    {
        return config('audit.categories', []);
    }

    /**
     * Resolve the target Eloquent model class for a given normalized section.
     */
    public static function resolveModelClass(?string $section): string
    {
        foreach (self::categories() as $category) {
            if (in_array($section, $category['sections'], true)) {
                return $category['model'];
            }
        }

        // Fallback: settings category (audit_logs table was dropped)
        return AuditSettingsLog::class;
    }

    /**
     * Resolve the target database table for a given normalized section.
     */
    public static function resolveTable(?string $section): string
    {
        foreach (self::categories() as $category) {
            if (in_array($section, $category['sections'], true)) {
                return $category['table'];
            }
        }

        return 'audit_settings_logs';
    }

    /**
     * Get a collection of distinct sections across all audit tables.
     */
    public static function allDistinctSections(): array
    {
        $sections = [];

        foreach (self::categories() as $category) {
            $modelClass = $category['model'];
            $results = $modelClass::query()
                ->whereNotNull('section')
                ->distinct()
                ->pluck('section')
                ->toArray();

            $sections = array_merge($sections, $results);
        }

        $sections = array_unique($sections);
        sort($sections);

        return $sections;
    }
}
