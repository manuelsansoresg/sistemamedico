<?php

namespace Tests\Feature;

use App\Models\AiConfig;
use App\Models\AiUsageLog;
use App\Models\Catalogo;
use App\Models\Suscripcion;
use App\Models\User;
use App\Services\AiService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'doctor']);
        Role::create(['name' => 'root']);
    }

    public function test_ensure_global_config_exists_creates_new_config(): void
    {
        $service = app(AiService::class);
        $config = $service->ensureConfigExists();

        $this->assertNotNull($config);
        $this->assertNull($config->user_id);
        $this->assertEquals('openai', $config->provider);
        $this->assertTrue($config->enabled);
    }

    public function test_ensure_global_config_exists_returns_existing_config(): void
    {
        $existing = AiConfig::create([
            'provider' => 'deepseek',
            'model_for_summary' => 'deepseek-chat',
        ]);

        $service = app(AiService::class);
        $config = $service->ensureConfigExists();

        $this->assertEquals($existing->id, $config->id);
        $this->assertEquals('deepseek', $config->provider);
    }

    public function test_can_use_ai_returns_false_without_api_key(): void
    {
        AiConfig::create([
            'provider' => 'openai',
            'model_for_summary' => 'gpt-4o-mini',
        ]);

        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        $service = app(AiService::class);

        $this->assertFalse($service->canUseAi($doctor, 'summary'));
    }

    public function test_can_use_ai_returns_true_for_root_with_config(): void
    {
        AiConfig::create([
            'provider' => 'openai',
            'api_key' => 'sk-test-key',
            'model_for_summary' => 'gpt-4o-mini',
        ]);

        $root = User::factory()->create();
        $root->assignRole('root');

        $service = app(AiService::class);

        $this->assertTrue($service->canUseAi($root, 'summary'));
    }

    public function test_send_request_logs_usage_and_audit(): void
    {
        AiConfig::create([
            'provider' => 'openai',
            'api_key' => 'sk-test-key',
            'model_for_summary' => 'gpt-4o-mini',
        ]);

        $doctor = User::factory()->create();
        $doctor->assignRole('root');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Test response']],
                ],
                'usage' => [
                    'prompt_tokens' => 10,
                    'completion_tokens' => 5,
                ],
                'model' => 'gpt-4o-mini',
            ], 200),
        ]);

        $service = app(AiService::class);
        $result = $service->sendRequest($doctor, 'summary', [
            ['role' => 'user', 'content' => 'Test prompt'],
        ]);

        $this->assertEquals('Test response', $result['content']);
        $this->assertEquals(10, $result['tokens_input']);
        $this->assertEquals(5, $result['tokens_output']);

        $this->assertDatabaseHas('ai_usage_logs', [
            'user_id' => $doctor->id,
            'action_type' => 'summary',
            'provider' => 'openai',
            'model_used' => 'gpt-4o-mini',
        ]);

        $this->assertDatabaseHas('audit_ai_logs', [
            'user_id' => $doctor->id,
            'section' => 'ia',
        ]);
    }

    public function test_subscription_service_tracks_ai_usage(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        $catalogo = Catalogo::create([
            'user_id' => $doctor->id,
            'nombre' => 'IA - Solicitudes de IA',
            'precio' => 0,
            'porcentaje_ganancia' => 0,
            'activo' => true,
        ]);

        Suscripcion::create([
            'user_id' => $doctor->id,
            'catalogo_id' => $catalogo->id,
            'cantidad' => 2,
            'tipo' => 'individual',
            'precio' => 0,
            'metodo_pago' => 'tarjeta',
            'estatus_pago' => 'pagado',
            'fecha_inicio' => now(),
        ]);

        AiUsageLog::create([
            'user_id' => $doctor->id,
            'action_type' => 'summary',
            'provider' => 'openai',
            'model_used' => 'gpt-4o-mini',
            'tokens_input' => 10,
            'tokens_output' => 5,
        ]);

        AiUsageLog::create([
            'user_id' => $doctor->id,
            'action_type' => 'assistant',
            'provider' => 'openai',
            'model_used' => 'gpt-4o',
            'tokens_input' => 20,
            'tokens_output' => 15,
        ]);

        $subscriptionService = app(SubscriptionService::class);

        $this->assertFalse($subscriptionService->canUseAi($doctor));
    }

    public function test_ai_usage_stats_returns_correct_data(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        AiUsageLog::create([
            'user_id' => $doctor->id,
            'action_type' => 'summary',
            'provider' => 'openai',
            'model_used' => 'gpt-4o-mini',
            'tokens_input' => 100,
            'tokens_output' => 50,
            'cost_estimate' => 0.001,
        ]);

        AiUsageLog::create([
            'user_id' => $doctor->id,
            'action_type' => 'summary',
            'provider' => 'openai',
            'model_used' => 'gpt-4o-mini',
            'tokens_input' => 200,
            'tokens_output' => 100,
            'cost_estimate' => 0.002,
        ]);

        AiUsageLog::create([
            'user_id' => $doctor->id,
            'action_type' => 'assistant',
            'provider' => 'openai',
            'model_used' => 'gpt-4o',
            'tokens_input' => 300,
            'tokens_output' => 150,
            'cost_estimate' => 0.010,
        ]);

        $subscriptionService = app(SubscriptionService::class);
        $stats = $subscriptionService->getAiUsageStats($doctor);

        $this->assertEquals(3, $stats['total_requests']);
        $this->assertEquals(900, $stats['total_tokens']);
        $this->assertEquals(0.013, $stats['total_cost']);
        $this->assertEquals(2, $stats['by_action']['summary']['count']);
        $this->assertEquals(1, $stats['by_action']['assistant']['count']);
    }

    public function test_global_ai_usage_stats_returns_correct_data(): void
    {
        $doctor1 = User::factory()->create();
        $doctor1->assignRole('doctor');

        $doctor2 = User::factory()->create();
        $doctor2->assignRole('doctor');

        AiUsageLog::create([
            'user_id' => $doctor1->id,
            'action_type' => 'summary',
            'provider' => 'openai',
            'model_used' => 'gpt-4o-mini',
            'tokens_input' => 100,
            'tokens_output' => 50,
            'cost_estimate' => 0.001,
        ]);

        AiUsageLog::create([
            'user_id' => $doctor2->id,
            'action_type' => 'assistant',
            'provider' => 'openai',
            'model_used' => 'gpt-4o',
            'tokens_input' => 200,
            'tokens_output' => 100,
            'cost_estimate' => 0.005,
        ]);

        $subscriptionService = app(SubscriptionService::class);
        $stats = $subscriptionService->getGlobalAiUsageStats();

        $this->assertEquals(2, $stats['total_requests']);
        $this->assertEquals(450, $stats['total_tokens']);
        $this->assertEquals(0.006, $stats['total_cost']);
    }

    public function test_ai_config_get_model_for_returns_correct_model(): void
    {
        $config = AiConfig::create([
            'provider' => 'openai',
            'model_for_summary' => 'gpt-4o-mini',
            'model_for_assistant' => 'gpt-4o',
        ]);

        $this->assertEquals('gpt-4o-mini', $config->getModelFor('summary'));
        $this->assertEquals('gpt-4o', $config->getModelFor('assistant'));
        $this->assertNull($config->getModelFor('notes'));
    }

    public function test_ai_config_is_enabled_for(): void
    {
        $config = AiConfig::create([
            'provider' => 'openai',
            'enabled' => true,
            'model_for_summary' => 'gpt-4o-mini',
        ]);

        $this->assertTrue($config->isEnabledFor('summary'));
        $this->assertFalse($config->isEnabledFor('assistant'));

        $config->update(['enabled' => false]);
        $this->assertFalse($config->isEnabledFor('summary'));
    }

    public function test_ai_config_global_static_method(): void
    {
        $config = AiConfig::create([
            'provider' => 'openai',
            'model_for_summary' => 'gpt-4o-mini',
        ]);

        $global = AiConfig::global();

        $this->assertNotNull($global);
        $this->assertEquals($config->id, $global->id);
    }
}
