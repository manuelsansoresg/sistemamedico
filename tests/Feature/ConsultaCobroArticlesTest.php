<?php

namespace Tests\Feature;

use App\Models\ArticuloCobro;
use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Consultorio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ConsultaCobroArticlesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_staff_can_refresh_active_charge_articles_for_the_appointment_doctor(): void
    {
        Role::create(['name' => 'secretaria']);

        $doctor = User::factory()->create();
        $secretaria = User::factory()->create(['created_by' => $doctor->id]);
        $secretaria->assignRole('secretaria');
        $paciente = User::factory()->create();
        $clinica = Clinica::create([
            'nombre' => 'Clinica Centro',
            'direccion' => 'Av. Principal 123',
            'telefono' => '555-1000',
            'created_by' => $doctor->id,
        ]);
        $consultorio = Consultorio::create([
            'nombre' => 'Consultorio 1',
            'telefono' => '555-2000',
            'created_by' => $doctor->id,
        ]);
        $cita = Cita::create([
            'doctor_id' => $doctor->id,
            'paciente_id' => $paciente->id,
            'clinica_id' => $clinica->id,
            'consultorio_id' => $consultorio->id,
            'fecha' => now()->toDateString(),
            'hora_inicio' => '13:00:00',
            'hora_fin' => '13:30:00',
            'estado' => 'pendiente',
        ]);

        ArticuloCobro::create([
            'doctor_id' => $doctor->id,
            'nombre' => 'Dimoflax',
            'precio' => 500,
            'activo' => true,
        ]);
        ArticuloCobro::create([
            'doctor_id' => $doctor->id,
            'nombre' => 'Inactivo',
            'precio' => 100,
            'activo' => false,
        ]);

        $this->actingAs($secretaria)
            ->getJson(route('consulta-cobros.articulos.index', $cita))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'nombre' => 'Dimoflax',
                'precio' => 500,
                'label' => 'Dimoflax - $500.00',
            ])
            ->assertJsonMissing(['nombre' => 'Inactivo']);
    }
}
