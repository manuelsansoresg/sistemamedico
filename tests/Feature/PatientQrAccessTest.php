<?php

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Consulta;
use App\Models\Consultorio;
use App\Models\Especialidad;
use App\Models\Plantilla;
use App\Models\SharedExpedientePermission;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Notification;
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
        'perfil_compartido' => true,
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

function createActivePatientQrPermission(User $patient, ?User $doctor = null): SharedExpedientePermission
{
    return SharedExpedientePermission::create([
        'patient_id' => $patient->id,
        'doctor_id' => $doctor?->id,
        'permission_type' => SharedExpedientePermission::TYPE_READ,
        'status' => SharedExpedientePermission::STATUS_ACTIVE,
        'starts_at' => now(),
        'expires_at' => now()->addHours(5),
        'patient_terms_accepted_at' => now(),
        'patient_terms_hash' => hash('sha256', __('pacientes.qr.permissions.patient_terms')),
    ]);
}

it('lets the patient view and generate their permanent expediente QR', function (): void {
    $this->withoutVite();

    ['patient' => $patient] = createPatientQrRecord();

    $response = $this->actingAs($patient)->get(route('paciente.qr.show'));

    $response->assertOk()
        ->assertSee(__('pacientes.qr.title'))
        ->assertSee(__('common.breadcrumbs.home'))
        ->assertSee(__('pacientes.qr.warning'))
        ->assertSee('bg-[#FBF4EA]', false)
        ->assertSee(__('common.buttons.cancel'))
        ->assertSee(__('common.buttons.save'))
        ->assertSee('data:image/svg+xml;base64', false);

    expect($patient->refresh()->patient_public_token)->not->toBeNull();
});

it('filters patient dashboard records by doctor specialty', function (): void {
    $this->withoutVite();

    ['doctor' => $doctor, 'patient' => $patient, 'consulta' => $consulta] = createPatientQrRecord();

    $gastro = Especialidad::create([
        'nombre' => 'Gastroenterologia',
        'activo' => true,
    ]);
    $cardio = Especialidad::create([
        'nombre' => 'Cardiologia',
        'activo' => true,
    ]);
    Especialidad::create([
        'nombre' => 'Dermatologia',
        'activo' => true,
    ]);
    Especialidad::create([
        'nombre' => 'Inactiva',
        'activo' => false,
    ]);

    $doctor->forceFill(['especialidad_id' => $gastro->id])->save();

    $otherDoctor = User::factory()->create([
        'name' => 'Doctora Cardio',
        'especialidad_id' => $cardio->id,
    ]);
    $otherDoctor->assignRole('doctor');

    $otherAppointment = Cita::create([
        'doctor_id' => $otherDoctor->id,
        'paciente_id' => $patient->id,
        'clinica_id' => $consulta->cita->clinica_id,
        'consultorio_id' => $consulta->cita->consultorio_id,
        'fecha' => now()->toDateString(),
        'hora_inicio' => '10:00',
        'hora_fin' => '10:30',
        'motivo' => 'Motivo cardiologia',
        'estado' => 'atendida',
    ]);

    Consulta::create([
        'cita_id' => $otherAppointment->id,
        'doctor_id' => $otherDoctor->id,
        'paciente_id' => $patient->id,
        'plantilla_id' => $consulta->plantilla_id,
        'peso' => 70,
        'estatura' => 1.70,
    ]);

    $this->actingAs($patient)
        ->get(route('dashboard', ['especialidad_id' => $gastro->id]))
        ->assertOk()
        ->assertSee('Gastroenterologia')
        ->assertSee('Dermatologia')
        ->assertDontSee('Inactiva')
        ->assertSee('Motivo visible por QR')
        ->assertDontSee('Motivo cardiologia');
});

it('lets the active shared patient create an expediente permission with the minimum duration', function (): void {
    $this->withoutVite();
    Notification::fake();

    ['doctor' => $doctor, 'patient' => $patient] = createPatientQrRecord();

    $this->actingAs($patient)
        ->post(route('paciente.shared-permissions.store'), [
            'permission_type' => 'download',
            'doctor_id' => $doctor->id,
            'accept_terms' => '1',
        ])
        ->assertRedirect();

    $permission = SharedExpedientePermission::query()->first();

    expect($permission)
        ->not->toBeNull()
        ->permission_type->toBe(SharedExpedientePermission::TYPE_DOWNLOAD)
        ->doctor_id->toBe($doctor->id)
        ->and($permission->expires_at->greaterThanOrEqualTo(now()->addHours(5)->subMinute()))->toBeTrue();

    $this->assertDatabaseHas('shared_expediente_permission_acceptances', [
        'shared_expediente_permission_id' => $permission->id,
        'user_id' => $patient->id,
        'actor_role' => 'patient',
    ]);

    $this->assertDatabaseHas('shared_expediente_audit_logs', [
        'shared_expediente_permission_id' => $permission->id,
        'patient_id' => $patient->id,
        'doctor_id' => $doctor->id,
        'action' => 'created',
    ]);

    Notification::assertSentTo($doctor, SystemNotification::class);
});

it('searches doctors remotely with a short limited result set', function (): void {
    $this->withoutVite();

    ['doctor' => $doctor, 'patient' => $patient] = createPatientQrRecord();

    User::factory()->count(12)->create(['name' => 'Doctora QR Extra'])->each(function (User $user): void {
        $user->assignRole('doctor');
    });

    $this->actingAs($patient)
        ->getJson(route('paciente.qr.doctors.search', ['q' => 'QR']))
        ->assertOk()
        ->assertJsonCount(0);

    $response = $this->actingAs($patient)
        ->getJson(route('paciente.qr.doctors.search', ['q' => 'Doctora QR']))
        ->assertOk()
        ->assertJsonCount(10);

    expect(collect($response->json())->pluck('id'))->toContain($doctor->id);
});

it('requires protected access before showing a shared expediente token', function (): void {
    $this->withoutVite();

    ['patient' => $patient, 'doctor' => $doctor] = createPatientQrRecord();
    $token = $patient->regeneratePublicExpedienteToken();
    createActivePatientQrPermission($patient, $doctor);

    $this->get(route('public.expediente.show', $token))
        ->assertOk()
        ->assertSee(__('pacientes.qr.permissions.access_title'))
        ->assertDontSee('Motivo visible por QR');

    $this->actingAs($doctor)
        ->get(route('public.expediente.show', $token))
        ->assertOk()
        ->assertSee(__('pacientes.qr.permissions.doctor_terms'))
        ->assertDontSee('Motivo visible por QR');

    $response = $this->actingAs($doctor)->get(route('public.expediente.show', [$token, 'accept_terms' => '1']));

    $response->assertOk()
        ->assertSee(__('public.expediente.readonly_badge'))
        ->assertSee($patient->profile_photo_url)
        ->assertSee(__('public.expediente.privacy_notice'))
        ->assertSee('Renata')
        ->assertSee('Motivo visible por QR');
});

it('allows temporary code access for external doctors until the permission expires', function (): void {
    $this->withoutVite();

    ['patient' => $patient] = createPatientQrRecord();
    $token = $patient->regeneratePublicExpedienteToken();

    $permission = SharedExpedientePermission::create([
        'patient_id' => $patient->id,
        'permission_type' => SharedExpedientePermission::TYPE_READ,
        'status' => SharedExpedientePermission::STATUS_ACTIVE,
        'external_doctor_name' => 'Doctor externo',
        'temporary_access_code' => 'ABCD-1234',
        'starts_at' => now(),
        'expires_at' => now()->addHours(5),
        'patient_terms_accepted_at' => now(),
        'patient_terms_hash' => hash('sha256', __('pacientes.qr.permissions.patient_terms')),
    ]);

    $this->get(route('public.expediente.show', [$token, 'access_code' => $permission->temporary_access_code]))
        ->assertOk()
        ->assertSee(__('pacientes.qr.permissions.external_terms'))
        ->assertDontSee('Motivo visible por QR');

    $this->get(route('public.expediente.show', [
        $token,
        'access_code' => $permission->temporary_access_code,
        'accept_terms' => '1',
    ]))
        ->assertOk()
        ->assertSee('Motivo visible por QR');

    $permission->update(['expires_at' => now()->subMinute()]);

    $this->get(route('public.expediente.show', [$token, 'access_code' => 'ABCD-1234']))
        ->assertNotFound();
});

it('rejects invalid or regenerated public expediente tokens', function (): void {
    $this->withoutVite();

    ['patient' => $patient, 'doctor' => $doctor] = createPatientQrRecord();
    $oldToken = $patient->regeneratePublicExpedienteToken();
    createActivePatientQrPermission($patient, $doctor);

    $this->actingAs($patient)
        ->post(route('paciente.qr.regenerate'))
        ->assertRedirect();

    $newToken = $patient->refresh()->patient_public_token;

    expect($newToken)->not->toBe($oldToken);

    $this->get(route('public.expediente.show', $oldToken))->assertNotFound();
    $this->actingAs($doctor)->get(route('public.expediente.show', $newToken))->assertOk();
});

it('does not allow a public token to view another patient consultation', function (): void {
    $this->withoutVite();

    ['patient' => $patient] = createPatientQrRecord();
    ['consulta' => $otherConsulta] = createPatientQrRecord();
    $token = $patient->regeneratePublicExpedienteToken();
    createActivePatientQrPermission($patient, $patient->doctors->first());
    $doctor = $patient->doctors->first();

    $this->actingAs($doctor)->get(route('public.expediente.show', [$token, 'accept_terms' => '1']))->assertOk();

    $this->actingAs($doctor)->get(route('public.expediente.consultas.show', [$token, $otherConsulta]))
        ->assertNotFound();
});

it('only allows the active shared patient to view their QR', function (): void {
    $this->withoutVite();
    $this->withoutMiddleware(\App\Http\Middleware\CheckDoctorStatus::class);

    ['doctor' => $doctor, 'patient' => $patient] = createPatientQrRecord();

    $this->actingAs($patient)
        ->get(route('paciente.qr.show'))
        ->assertOk()
        ->assertSee(__('pacientes.qr.title'));

    $this->actingAs($doctor)
        ->get(route('pacientes.qr.show', $patient))
        ->assertForbidden();
});

it('blocks QR generation and public expediente access when shared profile is inactive', function (): void {
    $this->withoutVite();

    ['patient' => $patient] = createPatientQrRecord();
    $token = $patient->regeneratePublicExpedienteToken();

    $patient->forceFill(['perfil_compartido' => false])->save();

    $this->actingAs($patient)
        ->get(route('paciente.qr.show'))
        ->assertForbidden();

    $this->actingAs($patient)
        ->post(route('paciente.qr.regenerate'))
        ->assertForbidden();

    $this->get(route('public.expediente.show', $token))
        ->assertNotFound();
});

it('does not show the QR access action in shared profiles management', function (): void {
    $this->withoutVite();
    $this->withoutMiddleware(\App\Http\Middleware\CheckDoctorStatus::class);

    Role::firstOrCreate(['name' => 'root']);
    ['patient' => $patient] = createPatientQrRecord();

    $root = User::factory()->create();
    $root->assignRole('root');

    $this->actingAs($root)
        ->get(route('pacientes.shared.index', ['estado' => 'todos']))
        ->assertOk()
        ->assertDontSee(route('pacientes.qr.show', $patient), false);
});
