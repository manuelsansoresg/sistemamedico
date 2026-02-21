<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Consultorio;
use App\Models\DiaSinCita;
use App\Models\Horario;
use App\Models\Suscripcion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = Cita::with(['doctor', 'paciente', 'consultorio', 'clinica']);

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $query->where('doctor_id', $ownerId);
        }

        $citas = $query->latest()->paginate(10);

        return view('admin.citas.index', compact('citas'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;

            if ($this->doctorBlockedByCedula($ownerId)) {
                return redirect()->route('citas.index')
                    ->with('error', 'No puedes crear citas porque la cédula profesional del médico aún no ha sido validada.');
            }

            $doctors = User::where('id', $ownerId)->get();
            $pacientes = User::role('paciente')
                ->where(function ($q) use ($ownerId) {
                    $q->whereHas('doctors', function ($subQ) use ($ownerId) {
                        $subQ->where('users.id', $ownerId);
                    })
                        ->orWhere('created_by', $ownerId);
                })->get();

            $clinicas = Clinica::where('created_by', $ownerId)->where('activo', true)->get();
            $consultorios = Consultorio::where('created_by', $ownerId)->where('activo', true)->get();
        } else {
            $doctors = User::role('doctor')->get();
            $pacientes = User::role('paciente')->get();
            $clinicas = Clinica::where('activo', true)->get();
            $consultorios = Consultorio::where('activo', true)->get();
        }

        return view('admin.citas.create', compact('doctors', 'pacientes', 'consultorios', 'clinicas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'paciente_id' => 'required|exists:users,id',
            'consultorio_id' => 'required|exists:consultorios,id',
            'clinica_id' => 'required|exists:clinicas,id',
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'motivo' => 'nullable|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($this->doctorBlockedByCedula((int) $validated['doctor_id'])) {
            return back()
                ->withInput()
                ->withErrors(['doctor_id' => 'No se pueden crear citas para este médico hasta que su cédula profesional sea validada.']);
        }

        if ($user->hasRole(['asistente', 'secretaria'])) {
            if ($validated['doctor_id'] != $user->created_by) {
                abort(403, 'No puedes crear citas para otro doctor.');
            }
        }

        // Get schedule to determine duration
        $dayOfWeek = Carbon::parse($validated['fecha'])->dayOfWeek;
        $horario = Horario::where('user_id', $validated['doctor_id'])
            ->where('consultorio_id', $validated['consultorio_id'])
            ->where('dia', $dayOfWeek)
            ->first();

        // Check for DiaSinCita blockages
        $blockedDays = DiaSinCita::whereDate('fecha_inicio', '<=', $validated['fecha'])
            ->whereDate('fecha_fin', '>=', $validated['fecha'])
            ->whereHas('consultorios', function ($q) use ($validated) {
                $q->where('consultorios.id', $validated['consultorio_id']);
            })
            ->get();

        foreach ($blockedDays as $blockedDay) {
            if ($blockedDay->todo_el_dia) {
                return back()->withErrors(['hora_inicio' => 'El día está bloqueado: '.$blockedDay->motivo]);
            }

            $citaStart = Carbon::parse($validated['fecha'].' '.$validated['hora_inicio']);
            $duration = $horario ? $horario->duracion_minutos : 30; // Default fallback
            $citaEnd = $citaStart->copy()->addMinutes($duration);

            $blockStart = Carbon::parse($validated['fecha'].' '.$blockedDay->hora_inicio);
            $blockEnd = Carbon::parse($validated['fecha'].' '.$blockedDay->hora_fin);

            // Overlap check: start < blockEnd && end > blockStart
            if ($citaStart->lt($blockEnd) && $citaEnd->gt($blockStart)) {
                return back()->withErrors(['hora_inicio' => 'El horario seleccionado está bloqueado: '.$blockedDay->motivo]);
            }
        }

        // Calculate end time based on schedule duration
        // If schedule not found, default to 30 mins or handle error.
        // Existing logic assumed schedule exists or didn't use it for duration in saving?
        // Let's see original code. It used $horario later to set hora_fin.

        if (! $horario) {
            // If no schedule, maybe allow but default 30 mins? Or fail?
            // Original code: $horario = Horario::where...->first();
            // $horaFin = Carbon::parse($validated['hora_inicio'])->addMinutes($horario->duracion_minutos);
            // This would crash if $horario is null. So let's assume it's required.
            return back()->withErrors(['hora_inicio' => 'No hay horario configurado para este doctor/consultorio en esta fecha.']);
        }

        $horaFin = Carbon::parse($validated['hora_inicio'])->addMinutes($horario->duracion_minutos);

        $cita = new Cita;
        $cita->doctor_id = $validated['doctor_id'];
        $cita->paciente_id = $validated['paciente_id'];
        $cita->consultorio_id = $validated['consultorio_id'];
        $cita->clinica_id = $validated['clinica_id'];
        $cita->fecha = $validated['fecha'];
        $cita->hora_inicio = $validated['hora_inicio'];
        $cita->hora_fin = $horaFin->format('H:i');
        $cita->motivo = $validated['motivo'];
        $cita->status = 'programada';
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $cita->created_by = $user->id;
        $cita->save();

        return redirect()->route('admin.citas.index')
            ->with('success', 'Cita creada correctamente.');
    }

    public function show(Cita $cita)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            if ($cita->doctor_id !== $ownerId) {
                abort(403);
            }
        }

        return view('admin.citas.show', compact('cita'));
    }

    public function edit(Cita $cita)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;

            if ($cita->doctor_id !== $ownerId) {
                abort(403);
            }

            $doctors = User::where('id', $ownerId)->get();
            $pacientes = User::role('paciente')
                ->where(function ($q) use ($ownerId) {
                    $q->whereHas('doctors', function ($subQ) use ($ownerId) {
                        $subQ->where('users.id', $ownerId);
                    })
                        ->orWhere('created_by', $ownerId);
                })->get();

            $clinicas = Clinica::where('created_by', $ownerId)->where('activo', true)->get();
            $consultorios = Consultorio::where('created_by', $ownerId)->where('activo', true)->get();
        } else {
            $doctors = User::role('doctor')->get();
            $pacientes = User::role('paciente')->get();
            $clinicas = Clinica::where('activo', true)->get();
            $consultorios = Consultorio::where('activo', true)->get();
        }

        return view('admin.citas.edit', compact('cita', 'doctors', 'pacientes', 'consultorios', 'clinicas'));
    }

    public function update(Request $request, Cita $cita)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            if ($cita->doctor_id !== $ownerId) {
                abort(403);
            }
        }

        $validated = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'paciente_id' => 'required|exists:users,id',
            'consultorio_id' => 'required|exists:consultorios,id',
            'clinica_id' => 'required|exists:clinicas,id',
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'motivo' => 'nullable|string',
            'estado' => 'required|in:pendiente,confirmada,cancelada,completada',
        ]);

        if ($user->hasRole(['asistente', 'secretaria'])) {
            if ($validated['doctor_id'] != $user->created_by) {
                abort(403, 'No puedes asignar citas a otro doctor.');
            }
        }

        // If time/date/doctor changed, we need to validate availability again
        // But for now let's assume if they change it, they picked a valid slot from the UI.
        // We should ideally check if the slot is free (excluding this cita).

        $dayOfWeek = Carbon::parse($validated['fecha'])->dayOfWeek;
        $horario = Horario::where('user_id', $validated['doctor_id'])
            ->where('consultorio_id', $validated['consultorio_id'])
            ->where('dia', $dayOfWeek)
            ->first();

        // Check for DiaSinCita blockages
        $blockedDays = DiaSinCita::whereDate('fecha_inicio', '<=', $validated['fecha'])
            ->whereDate('fecha_fin', '>=', $validated['fecha'])
            ->whereHas('consultorios', function ($q) use ($validated) {
                $q->where('consultorios.id', $validated['consultorio_id']);
            })
            ->get();

        foreach ($blockedDays as $blockedDay) {
            if ($blockedDay->todo_el_dia) {
                return back()->withErrors(['hora_inicio' => 'El día está bloqueado: '.$blockedDay->motivo]);
            }

            $citaStart = Carbon::parse($validated['fecha'].' '.$validated['hora_inicio']);
            $duration = $horario ? $horario->duracion_minutos : 30; // Default fallback
            $citaEnd = $citaStart->copy()->addMinutes($duration);

            $blockStart = Carbon::parse($validated['fecha'].' '.$blockedDay->hora_inicio);
            $blockEnd = Carbon::parse($validated['fecha'].' '.$blockedDay->hora_fin);

            // Overlap check: start < blockEnd && end > blockStart
            if ($citaStart->lt($blockEnd) && $citaEnd->gt($blockStart)) {
                return back()->withErrors(['hora_inicio' => 'El horario seleccionado está bloqueado: '.$blockedDay->motivo]);
            }
        }

        if (! $horario) {
            return back()->withErrors(['hora_inicio' => 'No hay horario disponible para este médico en esta fecha/consultorio.']);
        }

        $start = Carbon::createFromFormat('H:i', $validated['hora_inicio']);
        $end = $start->copy()->addMinutes($horario->duracion_minutos);

        $validated['hora_fin'] = $end->format('H:i');

        $cita->update($validated);

        return redirect()->route('citas.index')->with('success', 'Cita actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cita $cita)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            if ($cita->doctor_id !== $ownerId) {
                abort(403, 'No tiene permiso para eliminar esta cita.');
            }
        }

        $cita->delete();

        return redirect()->route('citas.index')->with('success', 'Cita eliminada correctamente.');
    }

    /**
     * Search doctors by name.
     */
    public function searchDoctors(Request $request)
    {
        $search = $request->get('q');
        $doctors = User::role('doctor')
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'apellido_paterno', 'apellido_materno']);

        return response()->json($doctors);
    }

    /**
     * Search patients by name.
     */
    public function searchPatients(Request $request)
    {
        $search = $request->get('q');
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = User::role('paciente')
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $query->whereHas('doctors', function ($q) use ($ownerId) {
                $q->where('users.id', $ownerId);
            });
        }

        $patients = $query->limit(10)
            ->get(['id', 'name', 'apellido_paterno', 'apellido_materno']);

        return response()->json($patients);
    }

    /**
     * Get related data for a doctor (Consultorios, Clinicas).
     */
    public function getDoctorData($doctorId)
    {
        $doctor = User::findOrFail($doctorId);

        // If Auth user is doctor, only return created by Auth user (strictly ownership)
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('doctor')) {
            $allConsultorios = Consultorio::where('created_by', $user->id)->where('activo', true)->get();
            $allClinicas = Clinica::where('created_by', $user->id)->where('activo', true)->get();
        } else {
            // Root sees everything related to the doctor
            $assignedConsultorios = $doctor->consultorios()->where('activo', true)->get();
            $createdConsultorios = Consultorio::where('created_by', $doctorId)->where('activo', true)->get();
            $allConsultorios = $assignedConsultorios->merge($createdConsultorios)->unique('id')->values();

            $assignedClinicas = $doctor->clinicas()->where('activo', true)->get();
            $createdClinicas = Clinica::where('created_by', $doctorId)->where('activo', true)->get();
            $allClinicas = $assignedClinicas->merge($createdClinicas)->unique('id')->values();
        }

        return response()->json([
            'consultorios' => $allConsultorios,
            'clinicas' => $allClinicas,
        ]);
    }

    /**
     * Get available slots.
     */
    public function getSlots(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Validate input
            $request->validate([
                'doctor_id' => 'required|exists:users,id',
                'consultorio_id' => 'required|exists:consultorios,id',
                'fecha' => 'required|date',
            ]);

            $doctorId = (int) $request->doctor_id;
            $consultorioId = $request->consultorio_id;

            if ($this->doctorBlockedByCedula($doctorId)) {
                return response()->json([
                    'slots' => [],
                    'message' => 'No hay disponibilidad porque la cédula profesional del médico aún no ha sido validada.',
                    'debug' => ['blocked_cedula' => true],
                ]);
            }

            // Security check: if doctor, can only check own schedule?
            // Usually dashboard allows checking slots for anyone if allowed, but let's keep it open or restricted as per logic.
            // Existing code didn't have strict check here, but let's just fix the auth()->user() usage if it was there.
            // Actually, existing code had: $user = auth()->user();

            $date = Carbon::parse($request->fecha);
            $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) - 6 (Saturday)

            // Check for DiaSinCita blockages
            $blockedDays = DiaSinCita::whereDate('fecha_inicio', '<=', $date->format('Y-m-d'))
                ->whereDate('fecha_fin', '>=', $date->format('Y-m-d'))
                ->whereHas('consultorios', function ($q) use ($consultorioId) {
                    $q->where('consultorios.id', $consultorioId);
                })
                ->get();

            foreach ($blockedDays as $blockedDay) {
                if ($blockedDay->todo_el_dia) {
                    return response()->json([
                        'slots' => [],
                        'message' => 'El consultorio no tiene disponibilidad este día: '.$blockedDay->motivo,
                        'debug' => ['blocked' => true, 'reason' => $blockedDay->motivo],
                    ]);
                }
            }

            // Find schedules (plural)
            $horarios = Horario::where('user_id', $doctorId)
                ->where('consultorio_id', $consultorioId)
                ->where('dia', $dayOfWeek)
                ->get();

            $debug = [
                'doctor_id' => $doctorId,
                'consultorio_id' => $consultorioId,
                'fecha' => $date->format('Y-m-d'),
                'day_of_week' => $dayOfWeek,
                'horarios_found' => $horarios->count(),
                'horarios_details' => $horarios->toArray(),
            ];

            if ($horarios->isEmpty()) {
                return response()->json([
                    'slots' => [],
                    'message' => 'El médico no tiene horario este día en este consultorio.',
                    'debug' => $debug,
                ]);
            }

            $slots = [
                'Mañana' => [],
                'Tarde' => [],
                'Noche' => [],
            ];

            // Get existing appointments to exclude
            $query = Cita::where('doctor_id', $doctorId)
                ->where('consultorio_id', $consultorioId)
                ->where('fecha', $date->format('Y-m-d'))
                ->where('estado', '!=', 'cancelada');

            if ($request->has('except_cita_id')) {
                $query->where('id', '!=', $request->except_cita_id);
            }

            $existingCitas = $query->pluck('hora_inicio')
                ->map(function ($time) {
                    return Carbon::parse($time)->format('H:i');
                })
                ->toArray();

            foreach ($horarios as $horario) {
                // Generate slots for each schedule block
                $startTime = Carbon::parse($horario->hora_inicio);
                $endTime = Carbon::parse($horario->hora_fin);
                $duration = $horario->duracion_minutos;

                if ($duration <= 0) {
                    continue;
                } // Safety check

                $current = $startTime->copy();

                while ($current->copy()->addMinutes($duration)->lte($endTime)) {
                    $timeString = $current->format('H:i');

                    // Check if slot is taken
                    $isTaken = in_array($timeString, $existingCitas);

                    // Check if slot is blocked by DiaSinCita (partial day)
                    $isBlocked = false;
                    foreach ($blockedDays as $blockedDay) {
                        if (! $blockedDay->todo_el_dia) {
                            $blockStart = Carbon::parse($blockedDay->hora_inicio);
                            $blockEnd = Carbon::parse($blockedDay->hora_fin);

                            // Precise overlap check:
                            $slotEnd = $current->copy()->addMinutes($duration);
                            if ($current->lt($blockEnd) && $slotEnd->gt($blockStart)) {
                                $isBlocked = true;
                                break;
                            }
                        }
                    }

                    if (! $isTaken && ! $isBlocked) {
                        $hour = $current->hour;
                        if ($hour < 12) {
                            $slots['Mañana'][] = $timeString;
                        } elseif ($hour < 19) {
                            $slots['Tarde'][] = $timeString;
                        } else {
                            $slots['Noche'][] = $timeString;
                        }
                    }

                    $current->addMinutes($duration);
                }
            }

            // Sort and unique for each group
            foreach ($slots as $key => $periodSlots) {
                $unique = array_unique($periodSlots);
                sort($unique);
                $slots[$key] = $unique;
            }

            // Remove empty groups if desired, or keep them to show structure
            // For JSON response, let's keep keys but maybe we want to know if there are ANY slots
            $totalSlots = count($slots['Mañana']) + count($slots['Tarde']) + count($slots['Noche']);

            return response()->json(['slots' => $slots, 'total_slots' => $totalSlots, 'debug' => $debug]);
        } catch (\Exception $e) {
            return response()->json([
                'slots' => [],
                'message' => 'Error: '.$e->getMessage(),
                'debug' => ['exception' => $e->getTraceAsString()],
            ], 500);
        }
    }

    /**
     * Determina si un médico tiene bloqueada la creación de citas
     * por requerir validación de cédula aún no validada.
     */
    private function doctorBlockedByCedula(int $doctorId): bool
    {
        $doctor = User::find($doctorId);

        if (! $doctor) {
            return false;
        }

        $requiresCedula = Suscripcion::where('user_id', $doctorId)
            ->where('tipo', 'paquete')
            ->where('estatus_pago', 'pagado')
            ->where(function ($q) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', now());
            })
            ->with('paquete')
            ->get()
            ->contains(function ($s) {
                return optional($s->paquete)->validar_cedula === true;
            });

        if (! $requiresCedula) {
            return false;
        }

        return $doctor->estatus_cedula !== 'validada';
    }
}
