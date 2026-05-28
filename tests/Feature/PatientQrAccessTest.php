<?php

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Consulta;
use App\Models\Consultorio;
use App\Models\Plantilla;
use App\Models\User;
use Spatie\Permission\Models\Role;

function seedPatientQrRoles(): void
{
    Role::firstOrCreate(['name' => 'doctor']);
    Role::firstOrCreate(['name' => 'paciente']);
}

function createPatientQrRecord(): array
{
    seedPatientQrRoles();

    $doctor = User::factory()->create(['name' => 'Doctora QR']);
    $doctor->assignRole('doctor');

    $patient = User::factory()->create([
        'name' => 'Renata',
        'apellido_paterno' => 'Paciente',
        'created_by' => $doctor->id,
    ]);
    $patient->assignRole('paciente');
    $patient->doctors()->attach($doctor->id);

    $clinica = Clinica::create([
        'nombre' => 'Clinica QR',
        'direccion' => 'Direccion de prueba',
        'telefono' => '5555555555',
        'created_by' => $doctor->id,
    ]);

    $consultorio = Consultorio::create([
        'nombre' => 'Consultorio QR',
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
        'motivo' => 'Motivo visible por QR',
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
    ]);

    return compact('doctor', 'patient', 'consulta');
}

it('lets the patient view and generate their permanent expediente QR', function (): void {
    $this->withoutVite();

    ['patient' => $patient] = createPatientQrRecord();

    $response = $this->actingAs($patient)->get(route('paciente.qr.show'));

    $response->assertOk()
        ->assertSee(__('pacientes.qr.title'))
        ->assertSee(__('pacientes.qr.warning'))
        ->assertSee('bg-[#FBF4EA]', false)
        ->assertSee('data:image/svg+xml;base64', false);

    expect($patient->refresh()->patient_public_token)->not->toBeNull();
});

it('shows a read only public expediente with a valid token', function (): void {
    $this->withoutVite();

    ['patient' => $patient] = createPatientQrRecord();
    $token = $patient->regeneratePublicExpedienteToken();

    $response = $this->get(route('public.expediente.show', $token));

    $response->assertOk()
        ->assertSee(__('public.expediente.readonly_badge'))
        ->assertSee($patient->profile_photo_url)
        ->assertSee(__('public.expediente.privacy_notice'))
        ->assertSee('Renata')
        ->assertSee('Motivo visible por QR');
});

it('rejects invalid or regenerated public expediente tokens', function (): void {
    $this->withoutVite();

    ['patient' => $patient] = createPatientQrRecord();
    $oldToken = $patient->regeneratePublicExpedienteToken();

    $this->actingAs($patient)
        ->post(route('paciente.qr.regenerate'))
        ->assertRedirect();

    $newToken = $patient->refresh()->patient_public_token;

    expect($newToken)->not->toBe($oldToken);

    $this->get(route('public.expediente.show', $oldToken))->assertNotFound();
    $this->get(route('public.expediente.show', $newToken))->assertOk();
});

it('does not allow a public token to view another patient consultation', function (): void {
    $this->withoutVite();

    ['patient' => $patient] = createPatientQrRecord();
    ['consulta' => $otherConsulta] = createPatientQrRecord();
    $token = $patient->regeneratePublicExpedienteToken();

    $this->get(route('public.expediente.consultas.show', [$token, $otherConsulta]))
        ->assertNotFound();
});

it('allows related doctors to view the patient QR but blocks unrelated doctors', function (): void {
    $this->withoutVite();
    $this->withoutMiddleware(\App\Http\Middleware\CheckDoctorStatus::class);

    ['doctor' => $doctor, 'patient' => $patient] = createPatientQrRecord();

    $unrelatedDoctor = User::factory()->create();
    $unrelatedDoctor->assignRole('doctor');

    $this->actingAs($doctor)
        ->get(route('pacientes.qr.show', $patient))
        ->assertOk()
        ->assertSee(__('pacientes.qr.title'));

    $this->actingAs($unrelatedDoctor)
        ->get(route('pacientes.qr.show', $patient))
        ->assertForbidden();
});
