<?php

use App\Models\AiConfig;
use App\Models\AiUsageLog;
use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Consulta;
use App\Models\Consultorio;
use App\Models\Plantilla;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

function createAiSummaryRecord(): array
{
    Role::firstOrCreate(['name' => 'root']);
    Role::firstOrCreate(['name' => 'doctor']);
    Role::firstOrCreate(['name' => 'paciente']);

    AiConfig::create([
        'provider' => 'openai',
        'api_key' => 'sk-test-key',
        'model_for_summary' => 'gpt-4o-mini',
    ]);

    $root = User::factory()->create(['name' => 'Root IA']);
    $root->assignRole('root');

    $doctor = User::factory()->create(['name' => 'Doctora IA']);
    $doctor->assignRole('doctor');

    $patient = User::factory()->create([
        'name' => 'Renata',
        'apellido_paterno' => 'Paciente',
        'created_by' => $doctor->id,
    ]);
    $patient->assignRole('paciente');

    $clinica = Clinica::create([
        'nombre' => 'Clinica IA',
        'direccion' => 'Direccion',
        'telefono' => '5555555555',
        'created_by' => $doctor->id,
    ]);

    $consultorio = Consultorio::create([
        'nombre' => 'Consultorio IA',
        'telefono' => '5555555555',
        'created_by' => $doctor->id,
    ]);

    $plantilla = Plantilla::create([
        'nombre' => 'Consulta General',
        'user_id' => $doctor->id,
        'created_by' => $doctor->id,
    ]);

    $cita = Cita::create([
        'doctor_id' => $doctor->id,
        'paciente_id' => $patient->id,
        'clinica_id' => $clinica->id,
        'consultorio_id' => $consultorio->id,
        'fecha' => now()->toDateString(),
        'hora_inicio' => '09:00',
        'hora_fin' => '09:30',
        'motivo' => 'Dolor abdominal recurrente',
        'estado' => 'atendida',
    ]);

    $consulta = Consulta::create([
        'cita_id' => $cita->id,
        'doctor_id' => $doctor->id,
        'paciente_id' => $patient->id,
        'plantilla_id' => $plantilla->id,
        'peso' => 70,
        'estatura' => 1.70,
        'alergias' => 'Ninguna',
        'diagnostico' => 'Gastritis probable',
    ]);

    return compact('root', 'doctor', 'patient', 'consulta');
}

it('generates and caches a patient ai summary in usage metadata', function (): void {
    $this->withoutVite();

    ['root' => $root, 'patient' => $patient] = createAiSummaryRecord();

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Resumen breve del expediente.']],
            ],
            'usage' => [
                'prompt_tokens' => 40,
                'completion_tokens' => 12,
            ],
            'model' => 'gpt-4o-mini',
        ], 200),
    ]);

    $this->actingAs($root)
        ->get(route('expedientes.paciente.ai-summary', $patient))
        ->assertOk()
        ->assertSee('Resumen breve del expediente.')
        ->assertSee(__('ia.summary.generated'));

    expect(AiUsageLog::query()->count())->toBe(1)
        ->and(data_get(AiUsageLog::query()->first()->metadata, 'summary.content'))->toBe('Resumen breve del expediente.');

    $this->actingAs($root)
        ->get(route('expedientes.paciente.ai-summary', $patient))
        ->assertOk()
        ->assertSee('Resumen breve del expediente.')
        ->assertSee(__('ia.summary.cached'));

    Http::assertSentCount(1);
    expect(AiUsageLog::query()->count())->toBe(1);
});

it('shows the ai summary access in the expediente list when ai is available', function (): void {
    $this->withoutVite();

    ['root' => $root, 'patient' => $patient] = createAiSummaryRecord();

    $this->actingAs($root)
        ->get(route('expedientes.index'))
        ->assertOk()
        ->assertSee(route('expedientes.paciente.ai-summary', $patient), false)
        ->assertSee(__('ia.summary.action'));
});

it('regenerates the summary when the expediente changes', function (): void {
    $this->withoutVite();

    ['root' => $root, 'patient' => $patient, 'consulta' => $consulta] = createAiSummaryRecord();

    Http::fake([
        'api.openai.com/*' => Http::sequence()
            ->push([
                'choices' => [
                    ['message' => ['content' => 'Resumen inicial.']],
                ],
                'usage' => [
                    'prompt_tokens' => 40,
                    'completion_tokens' => 10,
                ],
                'model' => 'gpt-4o-mini',
            ], 200)
            ->push([
                'choices' => [
                    ['message' => ['content' => 'Resumen actualizado.']],
                ],
                'usage' => [
                    'prompt_tokens' => 42,
                    'completion_tokens' => 11,
                ],
                'model' => 'gpt-4o-mini',
            ], 200),
    ]);

    $this->actingAs($root)
        ->get(route('expedientes.paciente.ai-summary', $patient))
        ->assertOk()
        ->assertSee('Resumen inicial.');

    $this->travel(11)->minutes();

    $consulta->update(['diagnostico' => 'Gastritis con seguimiento actualizado']);

    $this->actingAs($root)
        ->get(route('expedientes.paciente.ai-summary', $patient))
        ->assertOk()
        ->assertSee('Resumen actualizado.');

    Http::assertSentCount(2);
    expect(AiUsageLog::query()->count())->toBe(2);
});

it('reuses a freshly generated summary to avoid burning tokens on immediate changes', function (): void {
    $this->withoutVite();

    ['root' => $root, 'patient' => $patient, 'consulta' => $consulta] = createAiSummaryRecord();

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Resumen recién generado.']],
            ],
            'usage' => [
                'prompt_tokens' => 40,
                'completion_tokens' => 10,
            ],
            'model' => 'gpt-4o-mini',
        ], 200),
    ]);

    $this->actingAs($root)
        ->get(route('expedientes.paciente.ai-summary', $patient))
        ->assertOk()
        ->assertSee('Resumen recién generado.');

    $consulta->update(['diagnostico' => 'Cambio inmediato']);

    $this->actingAs($root)
        ->get(route('expedientes.paciente.ai-summary', $patient))
        ->assertOk()
        ->assertSee('Resumen recién generado.')
        ->assertSee(__('ia.summary.cached'));

    Http::assertSentCount(1);
    expect(AiUsageLog::query()->count())->toBe(1);
});

it('uses the provided return url and label in the ai summary screen', function (): void {
    $this->withoutVite();

    ['root' => $root, 'patient' => $patient] = createAiSummaryRecord();

    Http::fake([
        'api.openai.com/*' => Http::response([
            'choices' => [
                ['message' => ['content' => 'Resumen con regreso dinámico.']],
            ],
            'usage' => [
                'prompt_tokens' => 40,
                'completion_tokens' => 10,
            ],
            'model' => 'gpt-4o-mini',
        ], 200),
    ]);

    $returnUrl = route('consultas.create', ['cita_id' => Cita::query()->where('paciente_id', $patient->id)->first()->id]);

    $this->actingAs($root)
        ->get(route('expedientes.paciente.ai-summary', [
            'paciente' => $patient,
            'return_url' => $returnUrl,
            'return_label' => 'Nueva consulta',
        ]))
        ->assertOk()
        ->assertSee($returnUrl, false)
        ->assertSee('Nueva consulta');
});
