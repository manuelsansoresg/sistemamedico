<?php

namespace App\Services;

use App\Models\Clinica;
use App\Models\Consultorio;
use App\Models\Suscripcion;
use App\Models\User;

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
        ];

        // Obtener suscripciones activas (Pagadas y Vigentes)
        $suscripciones = Suscripcion::where('user_id', $user->id)
            ->where('estatus_pago', 'pagado')
            ->where(function ($q) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', now());
            })
            ->with(['paquete.catalogos', 'catalogo'])
            ->get();

        foreach ($suscripciones as $sub) {
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
                $current = User::role('paciente')->where('created_by', $user->id)->count();
                break;
        }

        return $current < $limit;
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
            ->where('estatus_pago', 'pagado')
            ->where(function ($q) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', now());
            })
            ->exists();
    }

    public function pickOriginSubscription(User $user, string $type): ?Suscripcion
    {
        $key = $this->mapCatalogoToKey($type);

        if (! $key) {
            return null;
        }

        $suscripciones = Suscripcion::where('user_id', $user->id)
            ->where('estatus_pago', 'pagado')
            ->where(function ($q) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', now());
            })
            ->with(['paquete.catalogos', 'catalogo'])
            ->get();

        $extras = [];
        $paquetes = [];

        foreach ($suscripciones as $sub) {
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

        return null;
    }
}
