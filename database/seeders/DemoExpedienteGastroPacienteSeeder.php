<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Consulta;
use App\Models\ConsultaValor;
use App\Models\Consultorio;
use App\Models\Estudio;
use App\Models\Plantilla;
use App\Models\PlantillaCampo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoExpedienteGastroPacienteSeeder extends Seeder
{
    private const DOCTOR_EMAIL = 'irmalorenasosaiuit@gmail.com';

    private const PATIENT_EMAIL = 'paciente.gastro.demo@example.com';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->ensureRoles();

            $doctor = $this->doctor();
            $patient = $this->patient($doctor);
            $doctor->patients()->syncWithoutDetaching([$patient->id]);

            $clinic = $this->clinic($doctor);
            $consultorio = $this->consultorio($doctor);
            $plantilla = $this->plantilla($doctor);
            $campos = $this->campos($plantilla);

            $this->resetDemoExpediente($patient);

            foreach ($this->consultasDemo() as $item) {
                $cita = Cita::create([
                    'doctor_id' => $doctor->id,
                    'paciente_id' => $patient->id,
                    'consultorio_id' => $consultorio->id,
                    'clinica_id' => $clinic->id,
                    'fecha' => $item['fecha'],
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '09:30:00',
                    'motivo' => $item['motivo'],
                    'estado' => 'atendida',
                    'created_at' => $item['fecha'].' 09:00:00',
                    'updated_at' => $item['fecha'].' 09:30:00',
                ]);

                $consulta = Consulta::create([
                    'cita_id' => $cita->id,
                    'doctor_id' => $doctor->id,
                    'paciente_id' => $patient->id,
                    'plantilla_id' => $plantilla->id,
                    'peso' => $item['peso'],
                    'estatura' => 1.62,
                    'alergias' => 'Niega alergias medicamentosas conocidas.',
                    'diagnostico' => $item['diagnostico'],
                    'created_at' => $item['fecha'].' 09:28:00',
                    'updated_at' => $item['fecha'].' 09:28:00',
                ]);

                foreach ($item['valores'] as $slug => $valor) {
                    ConsultaValor::create([
                        'consulta_id' => $consulta->id,
                        'plantilla_campo_id' => $campos[$slug]->id,
                        'valor' => $valor,
                    ]);
                }

                if (! empty($item['estudio'])) {
                    Estudio::create([
                        'consulta_id' => $consulta->id,
                        'orden' => $item['estudio']['orden'],
                        'observacion' => $item['estudio']['observacion'],
                    ]);
                }
            }
        });
    }

    private function ensureRoles(): void
    {
        foreach (['doctor', 'paciente'] as $role) {
            Role::firstOrCreate(['name' => $role]);
        }
    }

    private function doctor(): User
    {
        $doctor = User::firstOrCreate(
            ['email' => self::DOCTOR_EMAIL],
            [
                'name' => 'Irma Lorena',
                'apellido_paterno' => 'Sosa',
                'apellido_materno' => 'Iuit',
                'password' => Hash::make('demo123456'),
                'telefono' => '9991234567',
                'cedula_profesional' => 'DEMO-IRMA-001',
                'activo' => true,
            ]
        );

        $doctor->assignRole('doctor');

        return $doctor;
    }

    private function patient(User $doctor): User
    {
        $patient = User::updateOrCreate(
            ['email' => self::PATIENT_EMAIL],
            [
                'name' => 'Mariana Isabel',
                'apellido_paterno' => 'Cetzal',
                'apellido_materno' => 'May',
                'password' => Hash::make('demo123456'),
                'telefono' => '9995550188',
                'fecha_nacimiento' => '1989-08-14',
                'sexo' => 'F',
                'direccion' => 'Calle 48 #215, Mérida, Yucatán',
                'numero_imss' => 'DEMOGASTRO01',
                'activo' => true,
                'perfil_compartido' => true,
                'created_by' => $doctor->id,
                'peso' => 68.40,
                'estatura' => 1.62,
                'alergias' => 'Niega alergias medicamentosas conocidas.',
            ]
        );

        $patient->assignRole('paciente');

        return $patient;
    }

    private function clinic(User $doctor): Clinica
    {
        $clinic = $doctor->clinicas()
            ->where('clinicas.created_by', $doctor->id)
            ->first()
            ?? Clinica::query()
                ->where('created_by', $doctor->id)
                ->first()
            ?? Clinica::firstOrCreate(
                [
                    'nombre' => 'Clínica Demo Gastro',
                    'created_by' => $doctor->id,
                ],
                [
                    'direccion' => 'Av. Demo 120, Mérida, Yucatán',
                    'telefono' => '9990001122',
                    'activo' => true,
                ]
            );

        $doctor->clinicas()->syncWithoutDetaching([$clinic->id]);

        return $clinic;
    }

    private function consultorio(User $doctor): Consultorio
    {
        $consultorio = $doctor->consultorios()
            ->where('consultorios.created_by', $doctor->id)
            ->first()
            ?? Consultorio::query()
                ->where('created_by', $doctor->id)
                ->first()
            ?? Consultorio::firstOrCreate(
                [
                    'nombre' => 'Consultorio Demo Gastro',
                    'created_by' => $doctor->id,
                ],
                [
                    'direccion' => 'Av. Demo 120, consultorio 2',
                    'telefono' => '9990001123',
                    'activo' => true,
                ]
            );

        $doctor->consultorios()->syncWithoutDetaching([$consultorio->id]);

        return $consultorio;
    }

    private function plantilla(User $doctor): Plantilla
    {
        return Plantilla::firstOrCreate(
            [
                'nombre' => 'Demo gastrointestinal',
                'user_id' => $doctor->id,
            ],
            [
                'created_by' => $doctor->id,
            ]
        );
    }

    /**
     * @return array<string, PlantillaCampo>
     */
    private function campos(Plantilla $plantilla): array
    {
        $campos = [
            ['nombre' => 'Motivo de consulta', 'slug' => 'motivo_consulta', 'tipo' => 'textarea', 'orden' => 1],
            ['nombre' => 'Síntomas digestivos', 'slug' => 'sintomas_digestivos', 'tipo' => 'textarea', 'orden' => 2],
            ['nombre' => 'Exploración abdominal', 'slug' => 'exploracion_abdominal', 'tipo' => 'textarea', 'orden' => 3],
            ['nombre' => 'Plan y tratamiento', 'slug' => 'plan_tratamiento', 'tipo' => 'textarea', 'orden' => 4],
            ['nombre' => 'Evolución', 'slug' => 'evolucion', 'tipo' => 'textarea', 'orden' => 5],
        ];

        return collect($campos)
            ->mapWithKeys(fn (array $campo): array => [
                $campo['slug'] => PlantillaCampo::updateOrCreate(
                    [
                        'plantilla_id' => $plantilla->id,
                        'slug' => $campo['slug'],
                    ],
                    [
                        'nombre' => $campo['nombre'],
                        'tipo' => $campo['tipo'],
                        'es_obligatorio' => false,
                        'orden' => $campo['orden'],
                    ]
                ),
            ])
            ->all();
    }

    private function resetDemoExpediente(User $patient): void
    {
        Consulta::query()
            ->where('paciente_id', $patient->id)
            ->whereHas('plantilla', fn ($query) => $query->where('nombre', 'Demo gastrointestinal'))
            ->get()
            ->each(fn (Consulta $consulta) => $consulta->delete());

        Cita::query()
            ->where('paciente_id', $patient->id)
            ->where('motivo', 'like', 'Demo gastrointestinal:%')
            ->delete();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function consultasDemo(): array
    {
        return [
            [
                'fecha' => now()->subMonths(8)->toDateString(),
                'peso' => 69.20,
                'motivo' => 'Demo gastrointestinal: dolor epigástrico y agruras',
                'diagnostico' => 'Dispepsia funcional probable con datos de reflujo gastroesofágico no erosivo.',
                'valores' => [
                    'motivo_consulta' => 'Dolor tipo ardor en epigastrio de 3 semanas de evolución, asociado a agruras posteriores a comidas abundantes y café.',
                    'sintomas_digestivos' => 'Pirosis 4 veces por semana, sensación de plenitud temprana, eructos frecuentes. Niega vómito persistente, melena, hematemesis o pérdida de peso involuntaria.',
                    'exploracion_abdominal' => 'Abdomen blando, depresible, dolor leve a palpación profunda en epigastrio, sin rebote ni defensa. Peristalsis presente.',
                    'plan_tratamiento' => 'Medidas higiénico-dietéticas: fraccionar comidas, evitar irritantes, café y cenar tarde. Omeprazol 20 mg cada 24 h por 4 semanas. Revalorar datos de alarma.',
                    'evolucion' => 'Primer contacto por cuadro compatible con dispepsia y reflujo. Se explica vigilancia de evacuaciones negras, vómito persistente o pérdida de peso.',
                ],
                'estudio' => [
                    'orden' => "Biometría hemática completa\nQuímica sanguínea\nPrueba de antígeno de Helicobacter pylori en heces",
                    'observacion' => 'Solicitados por persistencia de dispepsia y para descartar anemia o infección por H. pylori.',
                ],
            ],
            [
                'fecha' => now()->subMonths(7)->subDays(4)->toDateString(),
                'peso' => 68.90,
                'motivo' => 'Demo gastrointestinal: revisión de estudios y gastritis',
                'diagnostico' => 'Gastritis asociada a Helicobacter pylori, sin datos clínicos de sangrado digestivo.',
                'valores' => [
                    'motivo_consulta' => 'Acude con resultado positivo para antígeno de H. pylori. Refiere mejoría parcial de agruras con omeprazol.',
                    'sintomas_digestivos' => 'Dolor epigástrico intermitente, náusea ocasional sin vómito. Evacuaciones normales, sin sangre visible.',
                    'exploracion_abdominal' => 'Dolor epigástrico leve, sin masas, sin visceromegalias palpables. Signos vitales estables.',
                    'plan_tratamiento' => 'Terapia de erradicación por 14 días: IBP cada 12 h, amoxicilina y claritromicina según peso y tolerancia. Evitar alcohol e irritantes. Control posterior.',
                    'evolucion' => 'Se documenta H. pylori positivo. Se explican efectos secundarios esperados y necesidad de completar tratamiento.',
                ],
                'estudio' => [
                    'orden' => 'Prueba de antígeno de Helicobacter pylori en heces 4 semanas después de terminar tratamiento',
                    'observacion' => 'Control para confirmar erradicación, evitando IBP al menos 2 semanas antes si clínicamente es posible.',
                ],
            ],
            [
                'fecha' => now()->subMonths(6)->subDays(6)->toDateString(),
                'peso' => 68.30,
                'motivo' => 'Demo gastrointestinal: distensión abdominal',
                'diagnostico' => 'Síndrome de intestino irritable con predominio de distensión y alternancia evacuatoria.',
                'valores' => [
                    'motivo_consulta' => 'Posterior a tratamiento de H. pylori, refiere menos ardor pero distensión abdominal vespertina y gases.',
                    'sintomas_digestivos' => 'Alterna evacuaciones blandas con estreñimiento leve. Dolor cólico bajo que mejora al evacuar. Niega fiebre o sangrado.',
                    'exploracion_abdominal' => 'Abdomen globoso por gas, dolor leve difuso sin irritación peritoneal. Ruidos intestinales normales.',
                    'plan_tratamiento' => 'Diario de alimentos, reducción temporal de lácteos y bebidas carbonatadas, fibra soluble progresiva. Probiótico por 4 semanas. Manejo de estrés y sueño.',
                    'evolucion' => 'Se orienta cuadro funcional intestinal. Sin datos de alarma al interrogatorio ni exploración.',
                ],
                'estudio' => null,
            ],
            [
                'fecha' => now()->subMonths(4)->subDays(10)->toDateString(),
                'peso' => 67.80,
                'motivo' => 'Demo gastrointestinal: recaída por irritantes',
                'diagnostico' => 'Reflujo gastroesofágico con recaída relacionada a irritantes dietéticos.',
                'valores' => [
                    'motivo_consulta' => 'Reaparición de agruras después de periodo con comidas picantes, café y cenas tardías por trabajo.',
                    'sintomas_digestivos' => 'Pirosis nocturna 2 a 3 veces por semana, regurgitación ácida ocasional. Niega disfagia, vómito persistente o pérdida ponderal.',
                    'exploracion_abdominal' => 'Abdomen blando, dolor leve epigástrico. Orofaringe sin lesiones. Sin signos de deshidratación.',
                    'plan_tratamiento' => 'Retomar IBP por 2 semanas y plan dietético. Elevar cabecera, evitar acostarse antes de 2-3 horas después de cenar. Reforzar identificación de detonantes.',
                    'evolucion' => 'Recaída leve con relación temporal clara a irritantes. Se acuerda seguimiento si persisten síntomas.',
                ],
                'estudio' => null,
            ],
            [
                'fecha' => now()->subMonths(2)->subDays(18)->toDateString(),
                'peso' => 67.40,
                'motivo' => 'Demo gastrointestinal: dolor tipo cólico',
                'diagnostico' => 'Colitis funcional probable, sin criterios de urgencia abdominal.',
                'valores' => [
                    'motivo_consulta' => 'Dolor tipo cólico en hemiabdomen inferior asociado a periodos de estrés laboral.',
                    'sintomas_digestivos' => 'Distensión, meteorismo y sensación de evacuación incompleta. Evacuaciones sin moco ni sangre. Apetito conservado.',
                    'exploracion_abdominal' => 'Dolor leve en marco cólico, sin rebote. Peristalsis presente, abdomen sin masas palpables.',
                    'plan_tratamiento' => 'Hidratación, fibra soluble, actividad física. Butilhioscina solo si cólico intenso y sin contraindicaciones. Señales de alarma por escrito.',
                    'evolucion' => 'Cuadro compatible con trastorno funcional intestinal. Se insiste en seguimiento si aparece fiebre, sangre o dolor progresivo.',
                ],
                'estudio' => [
                    'orden' => "Coproparasitoscópico seriado de 3 muestras\nCoprológico funcional",
                    'observacion' => 'Solicitados por distensión persistente y cólico recurrente para descartar parasitosis o proceso infeccioso leve.',
                ],
            ],
            [
                'fecha' => now()->subWeeks(3)->toDateString(),
                'peso' => 67.10,
                'motivo' => 'Demo gastrointestinal: seguimiento estable',
                'diagnostico' => 'Dispepsia y colon irritable en control clínico con medidas dietéticas.',
                'valores' => [
                    'motivo_consulta' => 'Consulta de seguimiento. Refiere disminución importante de agruras y mejor control de distensión al evitar detonantes.',
                    'sintomas_digestivos' => 'Episodios leves de plenitud posprandial si consume irritantes. Evacuaciones regulares. Sin dolor nocturno ni datos de alarma.',
                    'exploracion_abdominal' => 'Abdomen blando, no doloroso, sin datos de irritación peritoneal. Peso estable.',
                    'plan_tratamiento' => 'Continuar medidas dietéticas, fibra soluble y registro de detonantes. IBP solo en ciclos cortos si recurre pirosis frecuente. Próximo control en 2-3 meses.',
                    'evolucion' => 'Buena respuesta global. Se documenta estabilidad y se refuerza educación sobre síntomas de alarma.',
                ],
                'estudio' => null,
            ],
        ];
    }
}
