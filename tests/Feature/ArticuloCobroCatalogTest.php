<?php

namespace Tests\Feature;

use App\Models\ArticuloCobro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArticuloCobroCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_articles_cannot_repeat_for_the_same_doctor_owner(): void
    {
        Role::create(['name' => 'secretaria']);

        $doctor = User::factory()->create();
        $secretaria = User::factory()->create(['created_by' => $doctor->id]);
        $secretaria->assignRole('secretaria');

        $this->actingAs($secretaria)
            ->post(route('articulos-cobro.store'), [
                'nombre' => ' Dimoflax ',
                'descripcion' => 'Medicamento para la dispepsia',
                'unidad' => '1',
                'precio' => 500,
                'activo' => 1,
            ])
            ->assertRedirect(route('articulos-cobro.index'));

        $this->actingAs($secretaria)
            ->post(route('articulos-cobro.store'), [
                'nombre' => 'Dimoflax',
                'descripcion' => 'Duplicado',
                'unidad' => '1',
                'precio' => 500,
                'activo' => 1,
            ])
            ->assertSessionHasErrors('nombre');

        $this->assertSame(1, ArticuloCobro::where('doctor_id', $doctor->id)->where('nombre', 'Dimoflax')->count());
    }

    public function test_same_article_name_is_allowed_for_different_doctors(): void
    {
        Role::create(['name' => 'secretaria']);

        $firstDoctor = User::factory()->create();
        $secondDoctor = User::factory()->create();
        $firstSecretary = User::factory()->create(['created_by' => $firstDoctor->id]);
        $secondSecretary = User::factory()->create(['created_by' => $secondDoctor->id]);
        $firstSecretary->assignRole('secretaria');
        $secondSecretary->assignRole('secretaria');

        foreach ([$firstSecretary, $secondSecretary] as $secretaria) {
            $this->actingAs($secretaria)
                ->post(route('articulos-cobro.store'), [
                    'nombre' => 'Dimoflax',
                    'descripcion' => 'Medicamento para la dispepsia',
                    'unidad' => '1',
                    'precio' => 500,
                    'activo' => 1,
                ])
                ->assertRedirect(route('articulos-cobro.index'));
        }

        $this->assertSame(2, ArticuloCobro::where('nombre', 'Dimoflax')->count());
    }
}
