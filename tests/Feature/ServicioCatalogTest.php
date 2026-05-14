<?php

namespace Tests\Feature;

use App\Models\Servicio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServicioCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_cannot_repeat_for_the_same_doctor_owner(): void
    {
        Role::create(['name' => 'doctor']);

        $doctor = User::factory()->create();
        $doctor->assignRole('doctor');

        $this->actingAs($doctor)
            ->post(route('servicios.store'), [
                'nombre' => ' Radiografia ',
                'duracion' => 25,
                'costo' => 280,
            ])
            ->assertRedirect(route('servicios.index'));

        $this->actingAs($doctor)
            ->post(route('servicios.store'), [
                'nombre' => 'Radiografia',
                'duracion' => 30,
                'costo' => 300,
            ])
            ->assertSessionHasErrors('nombre');

        $this->assertSame(1, Servicio::where('created_by', $doctor->id)->where('nombre', 'Radiografia')->count());
    }

    public function test_same_service_name_is_allowed_for_different_doctors(): void
    {
        Role::create(['name' => 'doctor']);

        $firstDoctor = User::factory()->create();
        $secondDoctor = User::factory()->create();
        $firstDoctor->assignRole('doctor');
        $secondDoctor->assignRole('doctor');

        foreach ([$firstDoctor, $secondDoctor] as $doctor) {
            $this->actingAs($doctor)
                ->post(route('servicios.store'), [
                    'nombre' => 'Radiografia',
                    'duracion' => 25,
                    'costo' => 280,
                ])
                ->assertRedirect(route('servicios.index'));
        }

        $this->assertSame(2, Servicio::where('nombre', 'Radiografia')->count());
    }
}
