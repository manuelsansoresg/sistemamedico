<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Especialidad;

class EspecialidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $especialidades = [
            ['nombre' => 'Medicina General', 'descripcion' => 'Atención primaria y general de la salud.'],
            ['nombre' => 'Pediatría', 'descripcion' => 'Atención médica de bebés, niños y adolescentes.'],
            ['nombre' => 'Ginecología y Obstetricia', 'descripcion' => 'Salud del sistema reproductor femenino.'],
            ['nombre' => 'Cardiología', 'descripcion' => 'Diagnóstico y tratamiento de enfermedades del corazón.'],
            ['nombre' => 'Dermatología', 'descripcion' => 'Enfermedades de la piel, cabello y uñas.'],
            ['nombre' => 'Oftalmología', 'descripcion' => 'Enfermedades de los ojos.'],
            ['nombre' => 'Ortopedia y Traumatología', 'descripcion' => 'Lesiones y enfermedades del sistema musculoesquelético.'],
            ['nombre' => 'Otorrinolaringología', 'descripcion' => 'Enfermedades del oído, nariz y garganta.'],
            ['nombre' => 'Psiquiatría', 'descripcion' => 'Trastornos mentales, emocionales y de comportamiento.'],
            ['nombre' => 'Neurología', 'descripcion' => 'Trastornos del sistema nervioso.'],
            ['nombre' => 'Gastroenterología', 'descripcion' => 'Enfermedades del sistema digestivo.'],
            ['nombre' => 'Endocrinología', 'descripcion' => 'Trastornos hormonales y del metabolismo.'],
            ['nombre' => 'Urología', 'descripcion' => 'Enfermedades del sistema urinario.'],
            ['nombre' => 'Neumología', 'descripcion' => 'Enfermedades del sistema respiratorio.'],
            ['nombre' => 'Nutrición', 'descripcion' => 'Asesoramiento dietético y nutricional.'],
            ['nombre' => 'Odontología', 'descripcion' => 'Salud bucodental.'],
            ['nombre' => 'Psicología', 'descripcion' => 'Salud mental y terapia psicológica.'],
            ['nombre' => 'Reumatología', 'descripcion' => 'Enfermedades de las articulaciones y tejidos conectivos.'],
            ['nombre' => 'Medicina Interna', 'descripcion' => 'Atención integral de adultos.'],
            ['nombre' => 'Cirugía General', 'descripcion' => 'Intervenciones quirúrgicas generales.'],
        ];

        foreach ($especialidades as $especialidad) {
            Especialidad::firstOrCreate(
                ['nombre' => $especialidad['nombre']],
                ['descripcion' => $especialidad['descripcion'], 'activo' => true]
            );
        }
    }
}
