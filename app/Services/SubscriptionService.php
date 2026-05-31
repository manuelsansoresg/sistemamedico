<?php

namespace App\Services;

use App\Models\Clinica;
use App\Models\Consultorio;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Calcular los límites efectivos del usuario basados en suscripciones activas.
     * Respeta la fecha de vencimiento y el estatus de pago.
     */
    public function calculateLimits(User $user)
    {
        // Lógica base: 0 si no hay suscripción
        $limites = [
            'clinicas' => 0,
            'consultorios' => 0,
            'usuarios' => 0,
            'pacientes' => 0,
            'ai_requests' => 0,
        ];

        // Obtener suscripciones activas (Pagadas y Vigentes)
        $suscripciones = Suscripcion::where('user_id', $user->id)
            ->pagadaVigente()
            ->with(['paquete.catalogos', 'catalogo'])
            ->get();

        foreach ($suscripciones as $sub) {
            if ($sub->fecha_fin && $sub->fecha_fin->lt(now())) {
                continue;
            }

            // Caso 1: Paquete
            if ($sub->tipo == 'paquete' && $sub->paquete) {
                foreach ($sub->paquete->catalogos as $cat) {
                    $key = $this->mapCatalogoToKey($cat->nombre);
                    if ($key) {
                        $limites[$key] += $cat->pivot->cantidad_maxima ?? 0;
                    }
                }
            }
            // Caso 2: Individual (Extras del catálogo)
            elseif ($sub->tipo == 'individual' && $sub->catalogo) {
                $key = $this->mapCatalogoToKey($sub->catalogo->nombre);
                if ($key) {
                    // Para individuales, la cantidad comprada se suma al límite
                    $limites[$key] += $sub->cantidad;
                }
            }
        }

        return $limites;
    }

    /**
     * Verificar si el usuario puede crear un nuevo recurso del tipo dado.
     * Retorna true si puede, false si alcanzó el límite.
     */
    public function canCreate(User $user, string $type)
    {
        if ($user->hasRole('root')) {
            return true;
        }

        $limites = $this->calculateLimits($user);
        $limitKey = $this->mapCatalogoToKey($type);

        if (! $limitKey || ! isset($limites[$limitKey])) {
            return false;
        }

        $limit = $limites[$limitKey];
        $current = 0;

        switch ($limitKey) {
            case 'clinicas':
                $current = Clinica::where('created_by', $user->id)->count();
                break;
            case 'consultorios':
                $current = Consultorio::where('created_by', $user->id)->count();
                break;
            case 'usuarios':
                $current = User::where('created_by', $user->id)
                    ->whereHas('roles', function ($q) {
                        $q->whereIn('name', ['asistente', 'secretaria']);
                    })->count();
                break;
            case 'pacientes':
                $current = $this->patientUsageCount($user);
                break;
        }

        return $current < $limit;
    }

    public function patientUsageCount(User $doctor): int
    {
        return User::role('paciente')
            ->where(function ($q) use ($doctor) {
                $q->where('created_by', $doctor->id)
                    ->orWhereHas('doctors', function ($subQ) use ($doctor) {
                        $subQ->where('users.id', $doctor->id);
                    });
            })
            ->distinct('users.id')
            ->count('users.id');
    }

    /**
     * Verificar si el usuario tiene activa alguna característica de catálogo.
     * Ejemplo de $feature: 'paciente', 'clinica'.
     */
    public function hasActiveFeature(User $user, string $feature): bool
    {
        $limites = $this->calculateLimits($user);
        $key = $this->mapCatalogoToKey($feature);

        if (! $key) {
            return false;
        }

        return ($limites[$key] ?? 0) > 0;
    }

    /**
     * Verificar si el usuario tiene un paquete activo.
     */
    public function hasActivePackage(User $user): bool
    {
        return Suscripcion::where('user_id', $user->id)
            ->where('tipo', 'paquete')
            ->pagadaVigente()
            ->exists();
    }

    public function patientHasActiveSubscription(User $patient): bool
    {
        $doctorIds = $patient->doctors()->pluck('users.id');

        if ($patient->created_by) {
            $doctorIds->push($patient->created_by);
        }

        $doctorIds = $doctorIds->unique()->values();

        if ($doctorIds->isEmpty()) {
            return false;
        }

        return $this->activePatientSubscriptionsForUsers($doctorIds->all())->exists();
    }

    public function availablePatientSubscription(User $doctor): ?Suscripcion
    {
        return $this->activePatientSubscriptionsForUsers([$doctor->id])
            ->with(['catalogo', 'paquete.catalogos'])
            ->get()
            ->first(function (Suscripcion $subscription) {
                $limit = $this->patientSubscriptionLimit($subscription);

                return $limit > 0 && $this->patientSubscriptionUsage($subscription) < $limit;
            });
    }

    public function assignPatientToSubscription(User $doctor, User $patient): ?Suscripcion
    {
        $currentSubscriptionId = DB::table('doctor_patient')
            ->where('doctor_id', $doctor->id)
            ->where('patient_id', $patient->id)
            ->value('suscripcion_id');

        if ($currentSubscriptionId) {
            $activeCurrentSubscription = $this->activePatientSubscriptionsForUsers([$doctor->id])
                ->where('suscripciones.id', $currentSubscriptionId)
                ->first();

            if ($activeCurrentSubscription) {
                return $activeCurrentSubscription;
            }
        }

        $subscription = $this->availablePatientSubscription($doctor);

        if (! $subscription) {
            return null;
        }

        if ($patient->doctors()->where('users.id', $doctor->id)->exists()) {
            $patient->doctors()->updateExistingPivot($doctor->id, ['suscripcion_id' => $subscription->id]);
        } else {
            $patient->doctors()->attach($doctor->id, ['suscripcion_id' => $subscription->id]);
        }

        return $subscription;
    }

    public function pickOriginSubscription(User $user, string $type): ?Suscripcion
    {
        $key = $this->mapCatalogoToKey($type);

        if (! $key) {
            return null;
        }

        $suscripciones = Suscripcion::where('user_id', $user->id)
            ->pagadaVigente()
            ->with(['paquete.catalogos', 'catalogo'])
            ->get();

        $extras = [];
        $paquetes = [];

        foreach ($suscripciones as $sub) {
            if ($sub->fecha_fin && $sub->fecha_fin->lt(now())) {
                continue;
            }

            if ($sub->tipo === 'individual' && $sub->catalogo) {
                $subKey = $this->mapCatalogoToKey($sub->catalogo->nombre);

                if ($subKey === $key) {
                    $extras[] = $sub;
                }
            } elseif ($sub->tipo === 'paquete' && $sub->paquete) {
                foreach ($sub->paquete->catalogos as $cat) {
                    $subKey = $this->mapCatalogoToKey($cat->nombre);

                    if ($subKey === $key && ($cat->pivot->cantidad_maxima ?? 0) > 0) {
                        $paquetes[] = $sub;
                        break;
                    }
                }
            }
        }

        if (count($extras) > 0) {
            return $extras[0];
        }

        if (count($paquetes) > 0) {
            return $paquetes[0];
        }

        return null;
    }

    private function mapCatalogoToKey($nombre)
    {
        $nombre = strtolower($nombre);
        if (str_contains($nombre, 'clínica') || str_contains($nombre, 'clinica')) {
            return 'clinicas';
        }
        if (str_contains($nombre, 'consultorio')) {
            return 'consultorios';
        }
        if (str_contains($nombre, 'usuario')) {
            return 'usuarios';
        }
        if (str_contains($nombre, 'paciente')) {
            return 'pacientes';
        }
        if (str_contains($nombre, 'ia') || str_contains($nombre, 'ai') || str_contains($nombre, 'inteligencia')) {
            return 'ai_requests';
        }

        return null;
    }

    private function activePatientSubscriptionsForUsers(array $doctorIds)
    {
        return Suscripcion::whereIn('user_id', $doctorIds)
            ->pagadaVigente()
            ->where(function ($q) {
                $q->where(function ($subQ) {
                    $subQ->where('tipo', 'individual')
                        ->whereHas('catalogo', function ($catalogoQ) {
                            $catalogoQ->whereRaw("LOWER(nombre) like '%paciente%'");
                        });
                })->orWhere(function ($subQ) {
                    $subQ->where('tipo', 'paquete')
                        ->whereHas('paquete.catalogos', function ($catalogoQ) {
                            $catalogoQ->whereRaw("LOWER(nombre) like '%paciente%'");
                        });
                });
            });
    }

    private function patientSubscriptionLimit(Suscripcion $subscription): int
    {
        if ($subscription->tipo === 'individual' && $subscription->catalogo) {
            return (int) ($subscription->cantidad ?? 0);
        }

        if ($subscription->tipo === 'paquete' && $subscription->paquete) {
            $patientCatalog = $subscription->paquete->catalogos
                ->first(fn ($catalogo) => str_contains(strtolower($catalogo->nombre), 'paciente'));

            return (int) ($patientCatalog?->pivot?->cantidad_maxima ?? 0);
        }

        return 0;
    }

    private function patientSubscriptionUsage(Suscripcion $subscription): int
    {
        return DB::table('doctor_patient')
            ->where('suscripcion_id', $subscription->id)
            ->count();
    }

    public function canUseAi(User $user): bool
    {
        if ($user->hasRole('root')) {
            return true;
        }

        $limites = $this->calculateLimits($user);
        $aiLimit = $limites['ai_requests'] ?? 0;

        if ($aiLimit <= 0) {
            return false;
        }

        $aiUsage = $this->getAiUsageCount($user);

        return $aiUsage < $aiLimit;
    }

    public function incrementAiUsage(User $user): void
    {
        if ($user->hasRole('root')) {
            return;
        }

        $this->getAiUsageCount($user, true);
    }

    public function getAiUsageCount(User $user, bool $increment = false): int
    {
        static $cache = [];

        $cacheKey = "ai_usage_{$user->id}";

        if (! $increment && isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $count = \App\Models\AiUsageLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMonth())
            ->count();

        if ($increment) {
            $count++;
        }

        $cache[$cacheKey] = $count;

        return $count;
    }

    public function getAiUsageStats(User $user, ?string $period = null): array
    {
        $query = \App\Models\AiUsageLog::where('user_id', $user->id);

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->subWeek());
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', now()->subMonth());
        }

        $logs = $query->get();

        $totalRequests = $logs->count();
        $totalTokens = $logs->sum(fn ($log) => $log->totalTokens());
        $totalCost = $logs->sum('cost_estimate');

        $byAction = $logs->groupBy('action_type')->map(fn ($group) => [
            'count' => $group->count(),
            'tokens' => $group->sum(fn ($log) => $log->totalTokens()),
            'cost' => $group->sum('cost_estimate'),
        ]);

        return [
            'total_requests' => $totalRequests,
            'total_tokens' => $totalTokens,
            'total_cost' => round($totalCost, 4),
            'by_action' => $byAction,
        ];
    }

    public function getGlobalAiUsageCount(bool $increment = false): int
    {
        static $globalCache = null;

        if (! $increment && $globalCache !== null) {
            return $globalCache;
        }

        $count = \App\Models\AiUsageLog::where('created_at', '>=', now()->subMonth())
            ->count();

        if ($increment) {
            $count++;
        }

        $globalCache = $count;

        return $count;
    }

    public function getGlobalAiUsageStats(?string $period = null): array
    {
        $query = \App\Models\AiUsageLog::query();

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->subWeek());
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', now()->subMonth());
        }

        $logs = $query->with(['user', 'patient'])->get();

        $totalRequests = $logs->count();
        $totalTokens = $logs->sum(fn ($log) => $log->totalTokens());
        $totalCost = $logs->sum('cost_estimate');

        $byAction = $logs->groupBy('action_type')->map(fn ($group) => [
            'count' => $group->count(),
            'tokens' => $group->sum(fn ($log) => $log->totalTokens()),
            'cost' => $group->sum('cost_estimate'),
        ]);

        return [
            'total_requests' => $totalRequests,
            'total_tokens' => $totalTokens,
            'total_cost' => round($totalCost, 4),
            'by_action' => $byAction,
        ];
    }

    public function getGlobalAiLimits(): array
    {
        return ['ai_requests' => 0];
    }
}
