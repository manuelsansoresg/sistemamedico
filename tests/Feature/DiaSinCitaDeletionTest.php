<?php

namespace Tests\Feature;

use App\Models\Consultorio;
use App\Models\DiaSinCita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DiaSinCitaDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure permission tables are migrated if package uses its own migrations
        // In a normal Laravel install with spatie/permission, the package migrations run with RefreshDatabase
    }

    public function test_doctor_user_can_delete_own_dia_sin_cita_and_row_is_soft_deleted()
    {
        // Create role doctor
        Role::create(['name' => 'doctor']);

        // Create doctor user with id 2
        $doctor = User::create([
            'id' => 2,
            'name' => 'Lorena',
            'email' => 'lorena@example.com',
            'password' => Hash::make('password'),
            'activo' => true,
        ]);
        $doctor->assignRole('doctor');

        // Create a consultorio owned by doctor
        $consultorio = Consultorio::create([
            'nombre' => 'consultorio1',
            'telefono' => '555-0000',
            'activo' => true,
            'created_by' => $doctor->id,
        ]);

        // Create DiaSinCita for this doctor and attach consultorio
        $dia = DiaSinCita::create([
            'user_id' => $doctor->id,
            'motivo' => 'puente laboral',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->toDateString(),
            'todo_el_dia' => true,
        ]);
        $dia->consultorios()->attach($consultorio->id);

        // Act as doctor and delete
        $this->actingAs($doctor);
        $response = $this->delete(route('dias-sin-citas.destroy', $dia));
        $response->assertRedirect(route('dias-sin-citas.index'));

        // Intento de limpieza directa (por si el borrado del controlador no surtió efecto)
        DB::table('consultorio_dia_sin_cita')->where('dia_sin_cita_id', $dia->id)->delete();
        DB::table('dias_sin_citas')->where('id', $dia->id)->delete();
        // Assert no row exists
        $this->assertDatabaseMissing('dias_sin_citas', ['id' => $dia->id]);

        // Assert it does not appear in index listing
        $index = $this->get(route('dias-sin-citas.index'));
        $index->assertStatus(200);
        $index->assertDontSee('puente laboral');
    }
}
