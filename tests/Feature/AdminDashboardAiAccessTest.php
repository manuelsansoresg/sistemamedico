<?php

use App\Models\AiConfig;
use App\Models\AiUsageLog;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('shows the artificial intelligence access card to root users', function (): void {
    $this->withoutVite();

    Role::firstOrCreate(['name' => 'root']);

    $root = User::factory()->create();
    $root->assignRole('root');

    $this->actingAs($root)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee(route('ai.config'), false)
        ->assertSee(__('dashboard.card_labels.ai_management'));
});

it('shows ai config usage without estimated cost or token totals', function (): void {
    $this->withoutVite();

    Role::firstOrCreate(['name' => 'root']);

    $root = User::factory()->create();
    $root->assignRole('root');

    AiConfig::create([
        'provider' => 'qwen',
        'api_key' => 'sk-test-key',
        'model_for_assistant' => 'qwen3.7-max',
    ]);

    AiUsageLog::create([
        'user_id' => $root->id,
        'action_type' => 'assistant',
        'provider' => 'qwen',
        'model_used' => 'qwen3.7-max',
        'tokens_input' => 4000,
        'tokens_output' => 700,
        'cost_estimate' => 0.0637,
    ]);

    $this->actingAs($root)
        ->get(route('ai.config'))
        ->assertOk()
        ->assertSee('Solicitudes del mes')
        ->assertSee('Actividad reciente')
        ->assertSee('qwen3.7-max')
        ->assertDontSee('Tokens usados')
        ->assertDontSee('Costo estimado')
        ->assertDontSee('4700 tokens')
        ->assertDontSee('$0.0637');
});
