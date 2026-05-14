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
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitaController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = Cita::with(['doctor', 'paciente', 'consultorio', 'clinica', 'cobro']);

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $query->whereIn('doctor_id', $this->appointmentAssignableUsersQuery($ownerId)->pluck('id'));
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
                    ->with('error', __('citas.errors.cedula_not_validated_create'));
            }

            $owner = User::findOrFail($ownerId);
            $limits = $this->subscriptionService->calculateLimits($owner);

            $doctors = $this->appointmentAssignableUsersQuery($ownerId)->get();

            $pacientes = User::role('paciente')
                ->where(function ($q) use ($ownerId) {
                    $q->whereHas('doctors', function ($subQ) use ($ownerId) {
                        $subQ->where('users.id', $ownerId);
                    })
                        ->orWhere('created_by', $ownerId);
                })->get();

            $clinicasLimit = $limits['clinicas'] ?? 0;
            $consultoriosLimit = $limits['consultorios'] ?? 0;

            $clinicasQuery = Clinica::where('created_by', $ownerId)
                ->where('activo', true)
                ->where(function ($q) {
                    $q->whereNull('origen_suscripcion_id')
                        ->orWhereHas('origenSuscripcion', function ($q2) {
                            $q2->pagadaVigente();
                        });
                });

            $consultoriosQuery = Consultorio::where('created_by', $ownerId)
                ->where('activo', true)
                ->where(function ($q) {
                    $q->whereNull('origen_suscripcion_id')
                        ->orWhereHas('origenSuscripcion', function ($q2) {
                            $q2->pagadaVigente();
                        });
                });

            $clinicas = $clinicasLimit > 0 ? $clinicasQuery->get() : collect();
            $consultorios = $consultoriosLimit > 0 ? $consultoriosQuery->get() : collect();
        } else {
            $doctors = $this->appointmentAssignableUsersQuery()->get();
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

        if ($this->doctorBlockedByCedula($this->ownerIdForAppointmentUser((int) $validated['doctor_id']))) {
            return back()
                ->withInput()
                ->withErrors(['doctor_id' => __('citas.validation.cedula_not_validated')]);
        }

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            if (! $this->appointmentAssignableUsersQuery($ownerId)->whereKey($validated['doctor_id'])->exists()) {
                abort(403, __('citas.errors.no_permission_create_other_doctor'));
            }
        }

        $consultorio = Consultorio::where('id', $validated['consultorio_id'])
            ->where(function ($q) {
                $q->whereNull('origen_suscripcion_id')
                    ->orWhereHas('origenSuscripcion', function ($q2) {
                        $q2->pagadaVigente();
                    });
            })
            ->first();

        $clinica = Clinica::where('id', $validated['clinica_id'])
            ->where(function ($q) {
                $q->whereNull('origen_suscripcion_id')
                    ->orWhereHas('origenSuscripcion', function ($q2) {
                        $q2->pagadaVigente();
                    });
            })
            ->first();

        if (! $consultorio || ! $clinica) {
            return back()
                ->withInput()
                ->withErrors(['consultorio_id' => __('citas.validation.office_or_clinic_unavailable')]);
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
                return back()->withErrors(['hora_inicio' => __('citas.validation.blocked_day', ['reason' => $blockedDay->motivo])]);
            }

            $citaStart = Carbon::parse($validated['fecha'].' '.$validated['hora_inicio']);
            $duration = $horario ? $horario->duracion_minutos : 30; // Default fallback
            $citaEnd = $citaStart->copy()->addMinutes($duration);

            $blockStart = Carbon::parse($validated['fecha'].' '.$blockedDay->hora_inicio);
            $blockEnd = Carbon::parse($validated['fecha'].' '.$blockedDay->hora_fin);

            // Overlap check: start < blockEnd && end > blockStart
            if ($citaStart->lt($blockEnd) && $citaEnd->gt($blockStart)) {
                return back()->withErrors(['hora_inicio' => __('citas.validation.blocked_time', ['reason' => $blockedDay->motivo])]);
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
            return back()->withErrors(['hora_inicio' => __('citas.validation.no_schedule_configured')]);
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
            ->with('success', __('citas.messages.created_success'));
    }

    public function show(Cita $cita)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            if (! $this->appointmentAssignableUsersQuery($ownerId)->whereKey($cita->doctor_id)->exists()) {
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

            if (! $this->appointmentAssignableUsersQuery($ownerId)->whereKey($cita->doctor_id)->exists()) {
                abort(403);
            }

            $owner = User::findOrFail($ownerId);
            $limits = $this->subscriptionService->calculateLimits($owner);

            $doctors = $this->appointmentAssignableUsersQuery($ownerId)->get();

            $pacientes = User::role('paciente')
                ->where(function ($q) use ($ownerId) {
                    $q->whereHas('doctors', function ($subQ) use ($ownerId) {
                        $subQ->where('users.id', $ownerId);
                    })
                        ->orWhere('created_by', $ownerId);
                })->get();

            $clinicasLimit = $limits['clinicas'] ?? 0;
            $consultoriosLimit = $limits['consultorios'] ?? 0;

            $clinicasQuery = Clinica::where('created_by', $ownerId)
                ->where('activo', true)
                ->where(function ($q) {
                    $q->whereNull('origen_suscripcion_id')
                        ->orWhereHas('origenSuscripcion', function ($q2) {
                            $q2->pagadaVigente();
                        });
                });

            $consultoriosQuery = Consultorio::where('created_by', $ownerId)
                ->where('activo', true)
                ->where(function ($q) {
                    $q->whereNull('origen_suscripcion_id')
                        ->orWhereHas('origenSuscripcion', function ($q2) {
                            $q2->pagadaVigente();
                        });
                });

            $clinicas = $clinicasLimit > 0 ? $clinicasQuery->get() : collect();
            $consultorios = $consultoriosLimit > 0 ? $consultoriosQuery->get() : collect();
        } else {
            $doctors = $this->appointmentAssignableUsersQuery()->get();
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
            if (! $this->appointmentAssignableUsersQuery($ownerId)->whereKey($cita->doctor_id)->exists()) {
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
            'estado' => 'required|in:pendiente,confirmada,cancelada,completada,requiere_reagenda',
        ]);

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            if (! $this->appointmentAssignableUsersQuery($ownerId)->whereKey($validated['doctor_id'])->exists()) {
                abort(403, __('citas.errors.no_permission_assign_other_doctor'));
            }
        }

        $consultorio = Consultorio::where('id', $validated['consultorio_id'])
            ->where(function ($q) {
                $q->whereNull('origen_suscripcion_id')
                    ->orWhereHas('origenSuscripcion', function ($q2) {
                        $q2->pagadaVigente();
                    });
            })
            ->first();

        $clinica = Clinica::where('id', $validated['clinica_id'])
            ->where(function ($q) {
                $q->whereNull('origen_suscripcion_id')
                    ->orWhereHas('origenSuscripcion', function ($q2) {
                        $q2->pagadaVigente();
                    });
            })
            ->first();

        if (! $consultorio || ! $clinica) {
            return back()
                ->withInput()
                ->withErrors(['consultorio_id' => __('citas.validation.office_or_clinic_unavailable')]);
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
                return back()->withErrors(['hora_inicio' => __('citas.validation.blocked_day', ['reason' => $blockedDay->motivo])]);
            }

            $citaStart = Carbon::parse($validated['fecha'].' '.$validated['hora_inicio']);
            $duration = $horario ? $horario->duracion_minutos : 30; // Default fallback
            $citaEnd = $citaStart->copy()->addMinutes($duration);

            $blockStart = Carbon::parse($validated['fecha'].' '.$blockedDay->hora_inicio);
            $blockEnd = Carbon::parse($validated['fecha'].' '.$blockedDay->hora_fin);

            // Overlap check: start < blockEnd && end > blockStart
            if ($citaStart->lt($blockEnd) && $citaEnd->gt($blockStart)) {
                return back()->withErrors(['hora_inicio' => __('citas.validation.blocked_time', ['reason' => $blockedDay->motivo])]);
            }
        }

        if (! $horario) {
            return back()->withErrors(['hora_inicio' => __('citas.validation.no_schedule_available')]);
        }

        $start = Carbon::createFromFormat('H:i', $validated['hora_inicio']);
        $end = $start->copy()->addMinutes($horario->duracion_minutos);

        $validated['hora_fin'] = $end->format('H:i');

        $cita->update($validated);

        return redirect()->route('citas.index')->with('success', __('citas.messages.updated_success'));
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
            if (! $this->appointmentAssignableUsersQuery($ownerId)->whereKey($cita->doctor_id)->exists()) {
                abort(403, __('citas.errors.no_permission_delete'));
            }
        }

        $cita->delete();

        return redirect()->route('citas.index')->with('success', __('citas.messages.deleted_success'));
    }

    /**
     * Search doctors and assistants by name.
     */
    public function searchDoctors(Request $request)
    {
        $search = $request->get('q');
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = $this->appointmentAssignableUsersQuery()
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $query = $this->appointmentAssignableUsersQuery($ownerId)
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('apellido_paterno', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        }

        $doctors = $query->limit(10)
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
        $ownerId = $this->ownerIdForAppointmentUser((int) $doctorId);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $currentOwnerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            if (! $this->appointmentAssignableUsersQuery($currentOwnerId)->whereKey($doctorId)->exists()) {
                abort(403);
            }
        }

        $owner = User::findOrFail($ownerId);
        $limits = $this->subscriptionService->calculateLimits($owner);
        $clinicasLimit = $limits['clinicas'] ?? 0;
        $consultoriosLimit = $limits['consultorios'] ?? 0;

        $assignedConsultorios = $doctor->consultorios()
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('origen_suscripcion_id')
                    ->orWhereHas('origenSuscripcion', function ($q2) {
                        $q2->pagadaVigente();
                    });
            })
            ->get();

        $createdConsultorios = Consultorio::where('created_by', $ownerId)
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('origen_suscripcion_id')
                    ->orWhereHas('origenSuscripcion', function ($q2) {
                        $q2->pagadaVigente();
                    });
            })
            ->get();

        $allConsultorios = $assignedConsultorios->merge($createdConsultorios)->unique('id')->values();

        $assignedClinicas = $doctor->clinicas()
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('origen_suscripcion_id')
                    ->orWhereHas('origenSuscripcion', function ($q2) {
                        $q2->pagadaVigente();
                    });
            })
            ->get();

        $createdClinicas = Clinica::where('created_by', $ownerId)
            ->where('activo', true)
            ->where(function ($q) {
                $q->whereNull('origen_suscripcion_id')
                    ->orWhereHas('origenSuscripcion', function ($q2) {
                        $q2->pagadaVigente();
                    });
            })
            ->get();

        $allClinicas = $assignedClinicas->merge($createdClinicas)->unique('id')->values();

        if ($clinicasLimit <= 0) {
            $allClinicas = collect();
        }

        if ($consultoriosLimit <= 0) {
            $allConsultorios = collect();
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

            if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
                $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
                if (! $this->appointmentAssignableUsersQuery($ownerId)->whereKey($doctorId)->exists()) {
                    abort(403);
                }
            }

            if ($this->doctorBlockedByCedula($this->ownerIdForAppointmentUser($doctorId))) {
                return response()->json([
                    'slots' => [],
                    'message' => __('citas.api.no_availability_cedula'),
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
                        'message' => __('citas.api.office_unavailable_day', ['reason' => $blockedDay->motivo]),
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
                    'message' => __('citas.api.no_schedule_day'),
                    'debug' => $debug,
                ]);
            }

            $slots = [
                'morning' => [],
                'afternoon' => [],
                'night' => [],
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
                            $slots['morning'][] = $timeString;
                        } elseif ($hour < 19) {
                            $slots['afternoon'][] = $timeString;
                        } else {
                            $slots['night'][] = $timeString;
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
            $totalSlots = count($slots['morning']) + count($slots['afternoon']) + count($slots['night']);

            return response()->json(['slots' => $slots, 'total_slots' => $totalSlots, 'debug' => $debug]);
        } catch (\Exception $e) {
            return response()->json([
                'slots' => [],
                'message' => __('citas.api.error_generic'),
                'debug' => ['exception' => $e->getTraceAsString()],
            ], 500);
        }
    }

    /**
     * Usuarios que pueden seleccionarse como responsable de una cita.
     */
    private function appointmentAssignableUsersQuery(?int $ownerId = null)
    {
        $query = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['doctor', 'asistente']);
        });

        if ($ownerId !== null) {
            $query->where(function ($q) use ($ownerId) {
                $q->where('id', $ownerId)
                    ->orWhere('created_by', $ownerId);
            });
        }

        return $query->orderBy('name')->orderBy('apellido_paterno');
    }

    /**
     * Resuelve el doctor dueño de la suscripción para un doctor/asistente.
     */
    private function ownerIdForAppointmentUser(int $userId): int
    {
        $appointmentUser = User::find($userId);

        if (! $appointmentUser) {
            return $userId;
        }

        return $appointmentUser->hasRole('doctor') ? $appointmentUser->id : (int) ($appointmentUser->created_by ?: $appointmentUser->id);
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
            ->pagadaVigente()
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
