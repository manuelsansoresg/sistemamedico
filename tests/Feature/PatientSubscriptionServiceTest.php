<?php

namespace Tests\Feature;

use App\Models\Catalogo;
use App\Models\Suscripcion;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PatientSubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_linked_patient_with_null_pivot_uses_active_patient_subscription(): void
    {
        Role::create(['name' => 'doctor']);
        Role::create(['name' => 'paciente']);

        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        $patient = User::factory()->create(['created_by' => $doctor->id]);
        $patient->assignRole('paciente');
        $patient->doctors()->attach($doctor->id);

        $catalog = Catalogo::create([
            'user_id' => $doctor->id,
            'nombre' => 'Paciente',
            'precio' => 100,
            'porcentaje_ganancia' => 0,
            'activo' => true,
        ]);

        Suscripcion::create([
            'user_id' => $doctor->id,
            'catalogo_id' => $catalog->id,
            'cantidad' => 1,
            'tipo' => 'individual',
            'precio' => 100,
            'metodo_pago' => 'tarjeta',
            'estatus_pago' => 'pagado',
            'fecha_inicio' => now(),
        ]);

        $service = app(SubscriptionService::class);

        $this->assertTrue($service->patientHasActiveSubscription($patient));
        $this->assertSame(1, $service->patientUsageCount($doctor));
        $this->assertFalse($service->canCreate($doctor, 'paciente'));
    }

    public function test_assign_patient_to_subscription_updates_existing_pivot(): void
    {
        Role::create(['name' => 'doctor']);
        Role::create(['name' => 'paciente']);

        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        $patient = User::factory()->create(['created_by' => $doctor->id]);
        $patient->assignRole('paciente');
        $patient->doctors()->attach($doctor->id);

        $catalog = Catalogo::create([
            'user_id' => $doctor->id,
            'nombre' => 'Paciente',
            'precio' => 100,
            'porcentaje_ganancia' => 0,
            'activo' => true,
        ]);

        $subscription = Suscripcion::create([
            'user_id' => $doctor->id,
            'catalogo_id' => $catalog->id,
            'cantidad' => 1,
            'tipo' => 'individual',
            'precio' => 100,
            'metodo_pago' => 'tarjeta',
            'estatus_pago' => 'pagado',
            'fecha_inicio' => now(),
        ]);

        $assigned = app(SubscriptionService::class)->assignPatientToSubscription($doctor, $patient);

        $this->assertNotNull($assigned);
        $this->assertSame($subscription->id, $assigned->id);
        $this->assertDatabaseHas('doctor_patient', [
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'suscripcion_id' => $subscription->id,
        ]);
        $this->assertSame(1, DB::table('doctor_patient')->where('suscripcion_id', $subscription->id)->count());
    }
}
