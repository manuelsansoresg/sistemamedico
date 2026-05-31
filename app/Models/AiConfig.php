<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'api_key',
        'model_for_summary',
        'model_for_assistant',
        'model_for_notes',
        'model_for_diagnosis',
        'model_for_prescription',
        'enabled',
        'provider_options',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'provider_options' => 'array',
        'api_key' => 'encrypted',
    ];

    protected $attributes = [
        'enabled' => true,
    ];

    public static function actionLabels(): array
    {
        return [
            'summary' => 'Resumen de expediente',
            'assistant' => 'Asistente del médico',
            'notes' => 'Notas clínicas',
            'diagnosis' => 'Sugerencia de diagnóstico',
            'prescription' => 'Generación de receta',
        ];
    }

    public static function global(): ?self
    {
        return static::whereNull('user_id')->first();
    }

    public static function ensureGlobalExists(): self
    {
        $config = static::global();

        if ($config) {
            return $config;
        }

        $defaults = static::defaultModels()['openai'] ?? [];

        return static::create([
            'provider' => 'openai',
            ...$defaults,
        ]);
    }

    public static function defaultModels(): array
    {
        return [
            'openai' => [
                'model_for_summary' => 'gpt-4o-mini',
                'model_for_assistant' => 'gpt-4o',
                'model_for_notes' => 'gpt-4o-mini',
                'model_for_diagnosis' => 'gpt-4o',
                'model_for_prescription' => 'gpt-4o-mini',
            ],
            'deepseek' => [
                'model_for_summary' => 'deepseek-chat',
                'model_for_assistant' => 'deepseek-reasoner',
                'model_for_notes' => 'deepseek-chat',
                'model_for_diagnosis' => 'deepseek-reasoner',
                'model_for_prescription' => 'deepseek-chat',
            ],
            'anthropic' => [
                'model_for_summary' => 'claude-3-5-haiku-latest',
                'model_for_assistant' => 'claude-3-5-sonnet-latest',
                'model_for_notes' => 'claude-3-5-haiku-latest',
                'model_for_diagnosis' => 'claude-3-5-sonnet-latest',
                'model_for_prescription' => 'claude-3-5-haiku-latest',
            ],
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getModelFor(string $actionType): ?string
    {
        $column = "model_for_{$actionType}";

        return $this->{$column} ?? null;
    }

    public function isEnabledFor(string $actionType): bool
    {
        if (! $this->enabled) {
            return false;
        }

        return $this->getModelFor($actionType) !== null;
    }
}
