<?php

use App\Models\AiChatMessage;
use App\Models\AiConfig;
use App\Models\AiUsageLog;
use App\Models\Catalogo;
use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Consultorio;
use App\Models\Paquete;
use App\Models\Plantilla;
use App\Models\PlantillaCampo;
use App\Models\Suscripcion;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'root']);
    Role::firstOrCreate(['name' => 'doctor']);
});

function createConsultaAiFixture(): array
{
    $root = User::factory()->create();
    $root->assignRole('root');

    $doctor = User::factory()->create([
        'name' => 'Lorena',
        'apellido_paterno' => 'Sosa',
        'activo' => true,
    ]);
    $doctor->assignRole('doctor');

    $catalogoIa = Catalogo::create([
        'user_id' => $root->id,
        'nombre' => 'Inteligencia artificial',
        'precio' => 0,
        'porcentaje_ganancia' => 0,
        'activo' => true,
    ]);

    $paquete = Paquete::create([
        'user_id' => $root->id,
        'nombre' => 'Paquete clínica',
        'precio' => 0,
        'tipo' => 'clinica',
        'activo' => true,
    ]);

    $paquete->catalogos()->attach($catalogoIa->id, [
        'cantidad_maxima' => 20,
        'precio' => 0,
    ]);

    Suscripcion::create([
        'user_id' => $doctor->id,
        'paquete_id' => $paquete->id,
        'tipo' => 'paquete',
        'precio' => 0,
        'metodo_pago' => 'tarjeta',
        'estatus_pago' => 'pagado',
        'fecha_inicio' => now(),
    ]);

    $patient = User::factory()->create([
        'name' => 'Renata',
        'apellido_paterno' => 'Sansores',
        'apellido_materno' => 'Sosa',
        'sexo' => 'F',
        'fecha_nacimiento' => now()->subYears(42),
        'alergias' => 'Penicilina',
    ]);

    $clinic = Clinica::create([
        'nombre' => 'Esperanza',
        'direccion' => 'Centro',
        'telefono' => '9999999999',
        'created_by' => $doctor->id,
    ]);

    $consultorio = Consultorio::create([
        'nombre' => 'Consultorio 1',
        'telefono' => '9999999999',
        'created_by' => $doctor->id,
    ]);

    $cita = Cita::create([
        'doctor_id' => $doctor->id,
        'paciente_id' => $patient->id,
        'consultorio_id' => $consultorio->id,
        'clinica_id' => $clinic->id,
        'fecha' => now()->toDateString(),
        'hora_inicio' => '09:00:00',
        'estado' => 'confirmada',
    ]);

    $plantilla = Plantilla::create([
        'nombre' => 'Consulta general',
        'user_id' => $doctor->id,
        'created_by' => $doctor->id,
    ]);

    $campo = PlantillaCampo::create([
        'plantilla_id' => $plantilla->id,
        'nombre' => 'Diagnóstico',
        'slug' => 'diagnostico',
        'tipo' => 'textarea',
        'orden' => 1,
    ]);

    return compact('root', 'doctor', 'patient', 'clinic', 'consultorio', 'cita', 'plantilla', 'campo');
}

it('uses configured models for new consulta ai actions', function (): void {
    $config = AiConfig::create([
        'provider' => 'openai',
        'api_key' => 'sk-test-key',
        'model_for_field_suggest' => 'gpt-4o-mini-custom',
        'model_for_study_order' => 'gpt-4o-custom',
    ]);

    expect($config->getModelFor(AiService::ACTION_FIELD_SUGGEST))->toBe('gpt-4o-mini-custom')
        ->and($config->getModelFor(AiService::ACTION_STUDY_ORDER))->toBe('gpt-4o-custom');
});

it('falls back to provider defaults when consulta ai models are empty on existing configs', function (): void {
    $config = AiConfig::create([
        'provider' => 'openai',
        'api_key' => 'sk-test-key',
        'model_for_field_suggest' => null,
        'model_for_study_order' => null,
    ]);

    expect($config->getModelFor(AiService::ACTION_FIELD_SUGGEST))->toBe('gpt-4o-mini')
        ->and($config->getModelFor(AiService::ACTION_STUDY_ORDER))->toBe('gpt-4o');
});

it('supports qwen provider defaults and compatible chat completions', function (): void {
    $config = AiConfig::create([
        'provider' => 'qwen',
        'api_key' => 'sk-test-key',
        'model_for_field_suggest' => null,
        'model_for_study_order' => null,
    ]);

    expect(array_keys(AiService::PROVIDERS))->toContain('qwen')
        ->and($config->getModelFor(AiService::ACTION_FIELD_SUGGEST))->toBe('qwen-turbo')
        ->and($config->getModelFor(AiService::ACTION_STUDY_ORDER))->toBe('qwen-plus');

    Http::fake([
        'dashscope-us.aliyuncs.com/compatible-mode/v1/chat/completions' => Http::response([
            'choices' => [
                ['message' => ['content' => 'OK']],
            ],
            'usage' => [
                'prompt_tokens' => 5,
                'completion_tokens' => 1,
            ],
            'model' => 'qwen-plus',
        ], 200),
    ]);

    $response = app(AiService::class)->callProviderDirect('qwen', 'sk-test-key', 'qwen-plus', [
        ['role' => 'system', 'content' => 'Responde OK.'],
        ['role' => 'user', 'content' => 'Test'],
    ], ['max_tokens' => 10]);

    expect($response['content'])->toBe('OK')
        ->and($response['tokens_input'])->toBe(5)
        ->and($response['tokens_output'])->toBe(1);

    Http::assertSent(fn ($request): bool => $request->url() === 'https://dashscope-us.aliyuncs.com/compatible-mode/v1/chat/completions'
        && $request->hasHeader('Authorization', 'Bearer sk-test-key')
        && $request['model'] === 'qwen-plus');
});

it('shows qwen authentication details when dashscope rejects the key', function (): void {
    Http::fake([
        'dashscope-us.aliyuncs.com/compatible-mode/v1/chat/completions' => Http::response([
            'error' => [
                'message' => 'Incorrect API key provided.',
            ],
        ], 401),
    ]);

    app(AiService::class)->callProviderDirect('qwen', ' sk-test-key ', 'qwen-plus', [
        ['role' => 'system', 'content' => 'Responde OK.'],
        ['role' => 'user', 'content' => 'Test'],
    ], ['max_tokens' => 10]);
})->throws(Exception::class, 'Verifica que la API key pertenezca a US (Virginia)');

it('does not fall back for disabled legacy ai actions', function (): void {
    $config = AiConfig::create([
        'provider' => 'openai',
        'api_key' => 'sk-test-key',
        'model_for_summary' => null,
    ]);

    expect($config->getModelFor(AiService::ACTION_SUMMARY))->toBeNull();
});

it('renders consulta creation with the ai assistance controls', function (): void {
    $fixture = createConsultaAiFixture();

    $this->actingAs($fixture['doctor'])
        ->get(route('consultas.create', $fixture['cita']->id))
        ->assertOk()
        ->assertSee(__('consultas.ai.chat.title'))
        ->assertSee(__('consultas.ai.chat.open'))
        ->assertSee(__('consultas.ai.chat.copy'));
});

it('shows the patient ai summary access in the consultation header when available', function (): void {
    $fixture = createConsultaAiFixture();

    AiConfig::create([
        'provider' => 'openai',
        'api_key' => 'sk-test-key',
        'model_for_summary' => 'gpt-4o-mini',
    ]);

    $this->actingAs($fixture['doctor'])
        ->get(route('consultas.create', $fixture['cita']->id))
        ->assertOk()
        ->assertSee(__('ia.summary.action'))
        ->assertSee(route('expedientes.paciente.ai-summary', $fixture['patient']), false);
});

it('suggests a study order for the patient consultation context', function (): void {
    $fixture = createConsultaAiFixture();

    AiConfig::create([
        'provider' => 'openai',
        'api_key' => 'sk-test-key',
        'model_for_study_order' => 'gpt-4o',
    ]);

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => "Biometría hemática completa\nQuímica sanguínea\nUltrasonido abdominal"]],
            ],
            'usage' => [
                'prompt_tokens' => 18,
                'completion_tokens' => 14,
            ],
            'model' => 'gpt-4o',
        ], 200),
    ]);

    $this->actingAs($fixture['root'])
        ->postJson(route('consultas.ai.suggest-study-order'), [
            'cita_id' => $fixture['cita']->id,
            'diagnostico' => 'Dolor abdominal',
        ])
        ->assertOk()
        ->assertJsonPath('content', "Biometría hemática completa\nQuímica sanguínea\nUltrasonido abdominal");
});

it('answers the floating ai chat for the assigned doctor and current patient', function (): void {
    $fixture = createConsultaAiFixture();

    AiConfig::create([
        'provider' => 'openai',
        'api_key' => 'sk-test-key',
        'model_for_assistant' => 'gpt-4o',
    ]);

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Considera descartar causas digestivas y vigilar datos de alarma.']],
            ],
            'usage' => [
                'prompt_tokens' => 20,
                'completion_tokens' => 12,
            ],
            'model' => 'gpt-4o',
        ], 200),
    ]);

    $this->actingAs($fixture['doctor'])
        ->postJson(route('consultas.ai.chat'), [
            'cita_id' => $fixture['cita']->id,
            'message' => '¿Qué debo considerar por el dolor abdominal?',
            'messages' => [],
            'context' => [
                'valores_campos' => [
                    'Motivo' => 'Dolor abdominal',
                ],
            ],
        ])
        ->assertOk()
        ->assertJsonPath('content', 'Considera descartar causas digestivas y vigilar datos de alarma.');
});

it('accepts short valid answers from the floating ai chat', function (): void {
    $fixture = createConsultaAiFixture();

    AiConfig::create([
        'provider' => 'openai',
        'api_key' => 'sk-test-key',
        'model_for_assistant' => 'gpt-4o',
    ]);

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Sí.']],
            ],
            'usage' => [
                'prompt_tokens' => 20,
                'completion_tokens' => 1,
            ],
            'model' => 'gpt-4o',
        ], 200),
    ]);

    $this->actingAs($fixture['doctor'])
        ->postJson(route('consultas.ai.chat'), [
            'cita_id' => $fixture['cita']->id,
            'message' => '¿Puedes responder breve?',
            'messages' => [],
            'context' => [],
        ])
        ->assertOk()
        ->assertJsonPath('content', 'Sí.');
});

it('stores bounded ai chat memory and returns it for the consultation', function (): void {
    $fixture = createConsultaAiFixture();

    AiConfig::create([
        'provider' => 'openai',
        'api_key' => 'sk-test-key',
        'model_for_assistant' => 'gpt-4o',
    ]);

    Http::fake([
        'api.openai.com/*' => Http::sequence()
            ->push([
                'choices' => [
                    ['message' => ['content' => 'Primera respuesta guardada.']],
                ],
                'usage' => ['prompt_tokens' => 20, 'completion_tokens' => 8],
                'model' => 'gpt-4o',
            ], 200)
            ->push([
                'choices' => [
                    ['message' => ['content' => 'Segunda respuesta con memoria.']],
                ],
                'usage' => ['prompt_tokens' => 25, 'completion_tokens' => 9],
                'model' => 'gpt-4o',
            ], 200),
    ]);

    $this->actingAs($fixture['doctor'])
        ->postJson(route('consultas.ai.chat'), [
            'cita_id' => $fixture['cita']->id,
            'message' => 'Primera pregunta',
            'messages' => [],
        ])
        ->assertOk()
        ->assertJsonPath('content', 'Primera respuesta guardada.');

    $this->actingAs($fixture['doctor'])
        ->postJson(route('consultas.ai.chat'), [
            'cita_id' => $fixture['cita']->id,
            'message' => 'Segunda pregunta',
            'messages' => [],
        ])
        ->assertOk()
        ->assertJsonPath('content', 'Segunda respuesta con memoria.');

    $this->actingAs($fixture['doctor'])
        ->getJson(route('consultas.ai.chat.history', $fixture['cita']))
        ->assertOk()
        ->assertJsonCount(4, 'messages')
        ->assertJsonPath('messages.0.content', 'Primera pregunta')
        ->assertJsonPath('messages.1.content', 'Primera respuesta guardada.')
        ->assertJsonPath('messages.2.content', 'Segunda pregunta')
        ->assertJsonPath('messages.3.content', 'Segunda respuesta con memoria.');
});

it('keeps only the latest ai chat messages for a consultation', function (): void {
    $fixture = createConsultaAiFixture();

    foreach (range(1, 24) as $index) {
        AiChatMessage::create([
            'user_id' => $fixture['doctor']->id,
            'patient_id' => $fixture['patient']->id,
            'cita_id' => $fixture['cita']->id,
            'role' => $index % 2 === 0 ? 'assistant' : 'user',
            'content' => "Mensaje {$index}",
        ]);
    }

    AiConfig::create([
        'provider' => 'openai',
        'api_key' => 'sk-test-key',
        'model_for_assistant' => 'gpt-4o',
    ]);

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Respuesta nueva.']],
            ],
            'usage' => ['prompt_tokens' => 20, 'completion_tokens' => 8],
            'model' => 'gpt-4o',
        ], 200),
    ]);

    $this->actingAs($fixture['doctor'])
        ->postJson(route('consultas.ai.chat'), [
            'cita_id' => $fixture['cita']->id,
            'message' => 'Pregunta nueva',
            'messages' => [],
        ])
        ->assertOk();

    expect(AiChatMessage::query()
        ->where('user_id', $fixture['doctor']->id)
        ->where('cita_id', $fixture['cita']->id)
        ->count())->toBe(20);
});

it('returns a cached patient summary from the consultation chat without asking the assistant again', function (): void {
    $fixture = createConsultaAiFixture();

    AiConfig::create([
        'provider' => 'openai',
        'api_key' => 'sk-test-key',
        'model_for_summary' => 'gpt-4o-mini',
        'model_for_assistant' => 'gpt-4o',
    ]);

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Resumen clínico cacheable del paciente.']],
            ],
            'usage' => [
                'prompt_tokens' => 35,
                'completion_tokens' => 9,
            ],
            'model' => 'gpt-4o-mini',
        ], 200),
    ]);

    $payload = [
        'cita_id' => $fixture['cita']->id,
        'message' => 'Me puedes pasar su resumen clínico por favor',
        'messages' => [],
        'context' => [],
    ];

    $this->actingAs($fixture['doctor'])
        ->postJson(route('consultas.ai.chat'), $payload)
        ->assertOk()
        ->assertJsonPath('content', 'Resumen clínico cacheable del paciente.')
        ->assertJsonPath('summary_status', 'generated');

    $this->actingAs($fixture['doctor'])
        ->postJson(route('consultas.ai.chat'), $payload)
        ->assertOk()
        ->assertJsonPath('content', 'Resumen clínico cacheable del paciente.')
        ->assertJsonPath('summary_status', 'cached');

    Http::assertSentCount(1);

    expect(AiUsageLog::query())
        ->count()->toBe(1)
        ->and(AiUsageLog::query()->first()->action_type)->toBe(AiService::ACTION_SUMMARY);
});

it('does not allow root users to use the consultation ai chat', function (): void {
    $fixture = createConsultaAiFixture();

    $this->actingAs($fixture['root'])
        ->postJson(route('consultas.ai.chat'), [
            'cita_id' => $fixture['cita']->id,
            'message' => 'Ayúdame con esta consulta',
        ])
        ->assertForbidden();
});
