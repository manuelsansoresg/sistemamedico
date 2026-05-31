<?php

namespace App\Services;

use App\Models\AiUsageLog;
use App\Models\Consulta;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PatientAiSummaryService
{
    public function __construct(private readonly AiService $aiService) {}

    /**
     * @return array{content: string, status: string, generated_at: ?CarbonInterface, log: ?AiUsageLog}
     */
    public function getOrGenerate(User $user, User $patient): array
    {
        $snapshot = $this->buildSnapshot($patient);
        $sourceHash = hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE));

        $cachedLog = $this->findCachedSummary($user, $patient, $sourceHash);

        if ($cachedLog) {
            return [
                'content' => (string) data_get($cachedLog->metadata, 'summary.content'),
                'status' => 'cached',
                'generated_at' => $cachedLog->created_at,
                'log' => $cachedLog,
            ];
        }

        $response = $this->aiService->summarizeExpediente($user, $patient, $snapshot);
        $usageLog = AiUsageLog::query()->find($response['usage_log_id'] ?? null);

        if ($usageLog) {
            $usageLog->update([
                'metadata' => array_merge($usageLog->metadata ?? [], [
                    'summary' => [
                        'content' => trim($response['content'] ?? ''),
                        'source_hash' => $sourceHash,
                        'source_updated_at' => $this->sourceUpdatedAt($patient)?->toIso8601String(),
                        'consultas_incluidas' => count($snapshot['consultas']),
                        'consultas_totales' => $snapshot['total_consultas'],
                    ],
                ]),
            ]);
        }

        return [
            'content' => trim($response['content'] ?? ''),
            'status' => 'generated',
            'generated_at' => $usageLog?->created_at ?? now(),
            'log' => $usageLog,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSnapshot(User $patient): array
    {
        $consultas = $this->baseConsultasQuery($patient)
            ->limit(8)
            ->get();

        return [
            'source_updated_at' => $this->sourceUpdatedAt($patient)?->toIso8601String(),
            'paciente' => [
                'nombre' => trim("{$patient->name} {$patient->apellido_paterno} {$patient->apellido_materno}"),
                'fecha_nacimiento' => $patient->fecha_nacimiento?->format('d/m/Y'),
                'sexo' => $patient->sexo,
                'peso' => $patient->peso,
                'estatura' => $patient->estatura,
                'alergias' => Str::limit((string) $patient->alergias, 250, ''),
            ],
            'total_consultas' => Consulta::query()->where('paciente_id', $patient->id)->count(),
            'consultas' => $consultas->map(fn (Consulta $consulta): array => $this->formatConsulta($consulta))->values()->all(),
        ];
    }

    public function sourceUpdatedAt(User $patient): ?CarbonInterface
    {
        $latestConsulta = Consulta::query()
            ->where('paciente_id', $patient->id)
            ->max('updated_at');

        $latestCita = Consulta::query()
            ->join('citas', 'consultas.cita_id', '=', 'citas.id')
            ->where('consultas.paciente_id', $patient->id)
            ->max('citas.updated_at');

        $latestEstudio = Consulta::query()
            ->join('estudios', 'consultas.id', '=', 'estudios.consulta_id')
            ->where('consultas.paciente_id', $patient->id)
            ->max('estudios.updated_at');

        return collect([$patient->updated_at, $latestConsulta, $latestCita, $latestEstudio])
            ->filter()
            ->map(fn ($date) => $date instanceof CarbonInterface ? $date : \Carbon\Carbon::parse($date))
            ->sortDesc()
            ->first();
    }

    private function findCachedSummary(User $user, User $patient, string $sourceHash): ?AiUsageLog
    {
        return AiUsageLog::query()
            ->where('user_id', $user->id)
            ->where('patient_id', $patient->id)
            ->where('action_type', AiService::ACTION_SUMMARY)
            ->latest('created_at')
            ->get()
            ->first(function (AiUsageLog $log) use ($sourceHash): bool {
                return data_get($log->metadata, 'summary.source_hash') === $sourceHash
                    && filled(data_get($log->metadata, 'summary.content'));
            });
    }

    private function baseConsultasQuery(User $patient)
    {
        return Consulta::query()
            ->with(['cita.clinica', 'cita.consultorio', 'doctor', 'plantilla', 'estudios'])
            ->join('citas', 'consultas.cita_id', '=', 'citas.id')
            ->select('consultas.*')
            ->where('consultas.paciente_id', $patient->id)
            ->orderByDesc('citas.fecha')
            ->orderByDesc('consultas.id');
    }

    /**
     * @return array<string, mixed>
     */
    private function formatConsulta(Consulta $consulta): array
    {
        return [
            'fecha' => $consulta->cita?->fecha?->format('d/m/Y'),
            'doctor' => trim("{$consulta->doctor?->name} {$consulta->doctor?->apellido_paterno}"),
            'clinica' => $consulta->cita?->clinica?->nombre,
            'consultorio' => $consulta->cita?->consultorio?->nombre,
            'motivo' => Str::limit((string) $consulta->cita?->motivo, 200, ''),
            'plantilla' => $consulta->plantilla?->nombre,
            'diagnostico' => Str::limit((string) $consulta->diagnostico, 350, ''),
            'peso' => $consulta->peso,
            'estatura' => $consulta->estatura,
            'alergias' => Str::limit((string) $consulta->alergias, 180, ''),
            'estudios' => $this->formatEstudios($consulta->estudios),
        ];
    }

    private function formatEstudios(Collection $estudios): array
    {
        return $estudios
            ->take(3)
            ->map(fn ($estudio): array => [
                'orden' => Str::limit((string) $estudio->orden, 180, ''),
                'observacion' => Str::limit((string) $estudio->observacion, 180, ''),
            ])
            ->values()
            ->all();
    }
}
