<?php

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Consulta;
use App\Models\ConsultaValor;
use App\Models\Consultorio;
use App\Models\Estudio;
use App\Models\User;
use Database\Seeders\DemoExpedienteGastroPacienteSeeder;

it('creates a gastro demo expediente linked to doctora irma', function (): void {
    $this->seed(DemoExpedienteGastroPacienteSeeder::class);

    $doctor = User::where('email', 'irmalorenasosaiuit@gmail.com')->firstOrFail();
    $patient = User::where('email', 'paciente.gastro.demo@example.com')->firstOrFail();

    expect($doctor->hasRole('doctor'))->toBeTrue()
        ->and($patient->hasRole('paciente'))->toBeTrue()
        ->and($doctor->patients()->whereKey($patient->id)->exists())->toBeTrue()
        ->and($doctor->clinicas()->exists())->toBeTrue()
        ->and($doctor->consultorios()->exists())->toBeTrue()
        ->and(Consulta::where('paciente_id', $patient->id)->count())->toBe(6)
        ->and(Cita::where('paciente_id', $patient->id)->where('estado', 'atendida')->count())->toBe(6)
        ->and(ConsultaValor::whereHas('consulta', fn ($query) => $query->where('paciente_id', $patient->id))->count())->toBe(30)
        ->and(Estudio::whereHas('consulta', fn ($query) => $query->where('paciente_id', $patient->id))->count())->toBe(3);

    Cita::where('paciente_id', $patient->id)->get()->each(function (Cita $cita) use ($doctor): void {
        expect($doctor->clinicas()->whereKey($cita->clinica_id)->exists())->toBeTrue()
            ->and($doctor->consultorios()->whereKey($cita->consultorio_id)->exists())->toBeTrue();
    });

    expect(
        Consulta::where('paciente_id', $patient->id)
            ->pluck('diagnostico')
            ->implode(' ')
    )->toContain('Gastritis')
        ->toContain('Reflujo');
});

it('uses clinics and offices visible in doctora irma lists', function (): void {
    $otherDoctor = User::factory()->create();
    $irma = User::factory()->create([
        'email' => 'irmalorenasosaiuit@gmail.com',
        'name' => 'Irma',
        'apellido_paterno' => 'Sosa',
    ]);

    $externalClinic = Clinica::create([
        'nombre' => 'Esperanza',
        'direccion' => 'Externa',
        'telefono' => '9991111111',
        'created_by' => $otherDoctor->id,
    ]);

    $externalOffice = Consultorio::create([
        'nombre' => 'gastroenterología',
        'telefono' => '9992222222',
        'created_by' => $otherDoctor->id,
    ]);

    $visibleClinic = Clinica::create([
        'nombre' => 'mi clinica',
        'direccion' => 'Visible',
        'telefono' => '9999999999',
        'created_by' => $irma->id,
    ]);

    $visibleOffice = Consultorio::create([
        'nombre' => 'consultorio1',
        'telefono' => '9997887766',
        'created_by' => $irma->id,
    ]);

    $irma->clinicas()->syncWithoutDetaching([$externalClinic->id, $visibleClinic->id]);
    $irma->consultorios()->syncWithoutDetaching([$externalOffice->id, $visibleOffice->id]);

    $this->seed(DemoExpedienteGastroPacienteSeeder::class);

    $patient = User::where('email', 'paciente.gastro.demo@example.com')->firstOrFail();

    Cita::where('paciente_id', $patient->id)->get()->each(function (Cita $cita) use ($visibleClinic, $visibleOffice): void {
        expect($cita->clinica_id)->toBe($visibleClinic->id)
            ->and($cita->consultorio_id)->toBe($visibleOffice->id);
    });
});
