<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\CitaAfectacion;
use App\Models\ConsultaCobro;
use App\Models\ConsultaCobroItem;
use App\Models\Ganancia;
use App\Models\Servicio;
use App\Models\User;
use App\Notifications\CitaAfectadaNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ConsultaCobroService
{
    public function preview(Cita $cita, array $serviciosInput): array
    {
        $selectedServicios = $this->selectedServicios($cita, $serviciosInput);
        $duracionExtra = $selectedServicios->sum('duracion');
        $subtotalServicios = $selectedServicios->sum(function (Servicio $servicio) use ($serviciosInput): float {
            $precio = $this->priceForServicio($servicio, $serviciosInput[$servicio->id] ?? []);

            return (float) $precio;
        });

        $originalEnd = $this->originalEndForCita($cita);
        $projectedEnd = $originalEnd->copy()->addMinutes($duracionExtra);
        $affected = $this->affectedAppointments($cita, $projectedEnd, $originalEnd);

        return [
            'duracion_extra_minutos' => $duracionExtra,
            'subtotal_servicios' => round($subtotalServicios, 2),
            'hora_fin_original' => $originalEnd->format('H:i'),
            'hora_fin_proyectada' => $projectedEnd->format('H:i'),
            'affected' => $affected->map(fn (Cita $affectedCita): array => $this->affectedAppointmentPayload($affectedCita, $projectedEnd))->values()->all(),
        ];
    }

    public function saveDoctorCobro(Cita $cita, User $user, array $data): ConsultaCobro
    {
        return DB::transaction(function () use ($cita, $user, $data): ConsultaCobro {
            $cita->loadMissing('cobro', 'paciente');
            $existingCobro = $cita->cobro;
            $originalEnd = $this->originalEndForCita($cita);
            $selectedServicios = $this->selectedServicios($cita, $data['servicios'] ?? []);
            $duracionExtra = $selectedServicios->sum('duracion');
            $projectedEnd = $originalEnd->copy()->addMinutes($duracionExtra);

            $cobro = ConsultaCobro::updateOrCreate(
                ['cita_id' => $cita->id],
                [
                    'doctor_id' => $cita->doctor_id,
                    'paciente_id' => $cita->paciente_id,
                    'estado_instrucciones' => $data['estado_instrucciones'],
                    'instrucciones_cobro' => $data['estado_instrucciones'] === 'sin_instrucciones' ? null : ($data['instrucciones_cobro'] ?? null),
                    'hora_fin_original' => $existingCobro?->hora_fin_original ?: $originalEnd->format('H:i:s'),
                    'hora_fin_proyectada' => $projectedEnd->format('H:i:s'),
                    'duracion_extra_minutos' => $duracionExtra,
                    'estado_cobro' => 'pendiente',
                    'enviado_por' => $user->id,
                    'enviado_at' => now(),
                ]
            );

            $cobro->items()->where('tipo', 'servicio')->delete();

            foreach ($selectedServicios as $servicio) {
                $precioCobrado = $this->priceForServicio($servicio, $data['servicios'][$servicio->id] ?? []);
                $precioCatalogo = (float) $servicio->costo;

                $cobro->items()->create([
                    'tipo' => 'servicio',
                    'servicio_id' => $servicio->id,
                    'nombre_snapshot' => $servicio->nombre,
                    'cantidad' => 1,
                    'duracion_minutos_snapshot' => $servicio->duracion,
                    'precio_catalogo' => $precioCatalogo,
                    'precio_cobrado' => $precioCobrado,
                    'subtotal' => $precioCobrado,
                    'precio_modificado' => round($precioCatalogo, 2) !== round($precioCobrado, 2),
                    'modificado_por' => round($precioCatalogo, 2) !== round($precioCobrado, 2) ? $user->id : null,
                    'motivo_ajuste' => $data['servicios'][$servicio->id]['motivo_ajuste'] ?? null,
                ]);
            }

            $this->syncAfectaciones($cobro, $cita, $projectedEnd, $originalEnd);
            $this->recalculateTotals($cobro);

            $cita->update(['hora_fin' => $projectedEnd->format('H:i:s')]);

            return $cobro->fresh(['items', 'afectaciones.citaAfectada.paciente']);
        });
    }

    public function recalculateTotals(ConsultaCobro $cobro): void
    {
        $items = $cobro->items()->get();
        $subtotalServicios = $items->where('tipo', 'servicio')->sum(fn (ConsultaCobroItem $item): float => (float) $item->subtotal);
        $subtotalArticulos = $items->where('tipo', 'articulo')->sum(fn (ConsultaCobroItem $item): float => (float) $item->subtotal);
        $total = round($subtotalServicios + $subtotalArticulos, 2);

        $cobro->update([
            'subtotal_servicios' => $subtotalServicios,
            'subtotal_articulos' => $subtotalArticulos,
            'total' => $total,
        ]);

        $this->syncGanancia($cobro->fresh('cita'));
    }

    private function selectedServicios(Cita $cita, array $serviciosInput): Collection
    {
        $ids = collect($serviciosInput)
            ->filter(fn (array $payload): bool => isset($payload['selected']))
            ->keys()
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Servicio::whereIn('id', $ids)
            ->where('created_by', $cita->doctor_id)
            ->get();
    }

    private function priceForServicio(Servicio $servicio, array $payload): float
    {
        if (isset($payload['precio_cobrado']) && is_numeric($payload['precio_cobrado'])) {
            return round(max(0, (float) $payload['precio_cobrado']), 2);
        }

        return round((float) $servicio->costo, 2);
    }

    private function originalEndForCita(Cita $cita): Carbon
    {
        $cita->loadMissing('cobro');
        $date = $cita->fecha->format('Y-m-d');
        $time = $cita->cobro?->hora_fin_original ?: $cita->hora_fin?->format('H:i:s') ?: $cita->hora_inicio->format('H:i:s');

        return Carbon::parse($date.' '.$time);
    }

    private function affectedAppointments(Cita $cita, Carbon $projectedEnd, Carbon $originalEnd): Collection
    {
        if ($projectedEnd->lte($originalEnd)) {
            return collect();
        }

        return Cita::with('paciente')
            ->where('doctor_id', $cita->doctor_id)
            ->whereDate('fecha', $cita->fecha->format('Y-m-d'))
            ->whereKeyNot($cita->id)
            ->where('estado', '!=', 'cancelada')
            ->whereTime('hora_inicio', '>=', $originalEnd->format('H:i:s'))
            ->whereTime('hora_inicio', '<', $projectedEnd->format('H:i:s'))
            ->orderBy('hora_inicio')
            ->get();
    }

    private function syncAfectaciones(ConsultaCobro $cobro, Cita $cita, Carbon $projectedEnd, Carbon $originalEnd): void
    {
        $previousAffectations = $cobro->afectaciones()->get(['cita_afectada_id', 'estado_original']);
        $previousOriginalStatuses = $previousAffectations->pluck('estado_original', 'cita_afectada_id');
        $cobro->afectaciones()->delete();

        $affected = $this->affectedAppointments($cita, $projectedEnd, $originalEnd);
        $newAffectedIds = collect();

        foreach ($affected as $affectedCita) {
            $newAffectedIds->push($affectedCita->id);
            CitaAfectacion::create($this->affectedAppointmentRecord(
                $cobro,
                $cita,
                $affectedCita,
                $projectedEnd,
                $previousOriginalStatuses->get($affectedCita->id)
            ));
            $affectedCita->update(['estado' => 'requiere_reagenda']);
        }

        $this->restoreNoLongerAffectedAppointments($previousAffectations, $newAffectedIds);
        $this->notifySecretaries($cobro, $affected);
    }

    private function affectedAppointmentRecord(ConsultaCobro $cobro, Cita $cita, Cita $affectedCita, Carbon $projectedEnd, ?string $estadoOriginal = null): array
    {
        $patient = $affectedCita->paciente;

        return [
            'consulta_cobro_id' => $cobro->id,
            'cita_origen_id' => $cita->id,
            'cita_afectada_id' => $affectedCita->id,
            'paciente_afectado_id' => $affectedCita->paciente_id,
            'paciente_nombre_snapshot' => trim($patient->name.' '.$patient->apellido_paterno.' '.$patient->apellido_materno),
            'paciente_telefono_snapshot' => $patient->telefono,
            'paciente_email_snapshot' => $patient->email,
            'hora_inicio_original' => $affectedCita->hora_inicio->format('H:i:s'),
            'hora_fin_original' => $affectedCita->hora_fin?->format('H:i:s'),
            'estado_original' => $estadoOriginal ?: $affectedCita->estado,
            'hora_fin_origen_proyectada' => $projectedEnd->format('H:i:s'),
            'estado' => 'pendiente_aviso',
        ];
    }

    private function affectedAppointmentPayload(Cita $affectedCita, Carbon $projectedEnd): array
    {
        $patient = $affectedCita->paciente;

        return [
            'id' => $affectedCita->id,
            'paciente' => trim($patient->name.' '.$patient->apellido_paterno.' '.$patient->apellido_materno),
            'telefono' => $patient->telefono,
            'email' => $patient->email,
            'hora_inicio' => $affectedCita->hora_inicio->format('H:i'),
            'hora_fin' => $affectedCita->hora_fin?->format('H:i'),
            'hora_fin_proyectada' => $projectedEnd->format('H:i'),
        ];
    }

    private function restoreNoLongerAffectedAppointments(Collection $previousAffectations, Collection $newAffectedIds): void
    {
        foreach ($previousAffectations as $previousAffectation) {
            $appointmentId = $previousAffectation->cita_afectada_id;
            if ($newAffectedIds->contains($appointmentId)) {
                continue;
            }

            $hasOtherAffectations = CitaAfectacion::where('cita_afectada_id', $appointmentId)->exists();
            if (! $hasOtherAffectations) {
                Cita::whereKey($appointmentId)
                    ->where('estado', 'requiere_reagenda')
                    ->update(['estado' => $previousAffectation->estado_original ?: 'confirmada']);
            }
        }
    }

    private function notifySecretaries(ConsultaCobro $cobro, Collection $affected): void
    {
        if ($affected->isEmpty()) {
            return;
        }

        User::where('created_by', $cobro->doctor_id)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['secretaria', 'asistente']))
            ->get()
            ->each(fn (User $user) => $user->notify(new CitaAfectadaNotification($cobro, $affected->count())));
    }

    private function syncGanancia(ConsultaCobro $cobro): void
    {
        if ((float) $cobro->total <= 0) {
            Ganancia::where('consulta_cobro_id', $cobro->id)->delete();

            return;
        }

        Ganancia::updateOrCreate(
            ['consulta_cobro_id' => $cobro->id],
            [
                'user_id' => $cobro->doctor_id,
                'suscripcion_id' => null,
                'catalogo_id' => null,
                'paquete_id' => null,
                'monto_total' => $cobro->total,
                'monto_ganancia_doctor' => $cobro->total,
                'porcentaje_aplicado' => 100,
                'concepto' => __('cobros.earnings.consultation_charge', ['patient' => $cobro->paciente?->name ?? '']),
                'tipo_ingreso' => 'consulta',
                'fecha' => $cobro->cita?->fecha ?? now()->toDateString(),
            ]
        );
    }
}
