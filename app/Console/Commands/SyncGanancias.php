<?php

namespace App\Console\Commands;

use App\Models\Ganancia;
use App\Models\Suscripcion;
use Illuminate\Console\Command;

class SyncGanancias extends Command
{
    protected $signature = 'ganancias:sync';

    protected $description = 'Sincronizar ganancias para suscripciones pagadas existentes (Catálogos y Paquetes)';

    public function handle()
    {
        $this->info('Iniciando sincronización de ganancias...');

        // 1. Procesar Suscripciones de Catálogos
        $suscripcionesCatalogo = Suscripcion::where('estatus_pago', 'pagado')
            ->whereNotNull('catalogo_id')
            ->doesntHave('ganancia')
            ->with(['catalogo', 'user'])
            ->get();

        $this->processBatch($suscripcionesCatalogo, 'catalogo');

        // 2. Procesar Suscripciones de Paquetes
        $suscripcionesPaquete = Suscripcion::where('estatus_pago', 'pagado')
            ->whereNotNull('paquete_id')
            ->doesntHave('ganancia')
            ->with(['paquete', 'user'])
            ->get();

        $this->processBatch($suscripcionesPaquete, 'paquete');

        $this->info('Sincronización finalizada.');
    }

    private function processBatch($suscripciones, $type)
    {
        if ($suscripciones->isEmpty()) {
            $this->info("No se encontraron suscripciones de tipo {$type} pendientes.");

            return;
        }

        $count = 0;
        $this->info("Procesando {$suscripciones->count()} suscripciones de tipo {$type}...");

        foreach ($suscripciones as $sub) {
            $item = ($type === 'catalogo') ? $sub->catalogo : $sub->paquete;

            if (! $item) {
                continue;
            }

            $porcentaje = $item->porcentaje_ganancia ?? 0;
            $montoGananciaDoctor = 0;
            $porcentajeAplicado = 0;

            // Reglas:
            // Paquetes: 100% Root -> Doctor = 0
            if ($type === 'paquete') {
                $montoGananciaDoctor = 0;
                $porcentajeAplicado = 0;
            }
            // Catálogos
            else {
                if ($porcentaje == 0) {
                    // 0% -> Root 100%, Doctor 0%
                    $montoGananciaDoctor = 0;
                    $porcentajeAplicado = 0;
                } else {
                    // X% -> Doctor X%
                    $montoGananciaDoctor = $sub->precio * ($porcentaje / 100);
                    $porcentajeAplicado = $porcentaje;
                }
            }

            Ganancia::create([
                'user_id' => $sub->user_id,
                'suscripcion_id' => $sub->id,
                'catalogo_id' => ($type === 'catalogo') ? $item->id : null,
                'paquete_id' => ($type === 'paquete') ? $item->id : null,
                'monto_total' => $sub->precio,
                'monto_ganancia_doctor' => $montoGananciaDoctor,
                'porcentaje_aplicado' => $porcentajeAplicado,
                'concepto' => 'Ganancia por adquisición de: '.$item->nombre,
                'fecha' => $sub->fecha_inicio ?? $sub->created_at,
            ]);

            $count++;
            $this->info("Ganancia generada para suscripción ID: {$sub->id} ({$item->nombre})");
        }

        $this->info("Se generaron {$count} ganancias de tipo {$type}.");
    }
}
