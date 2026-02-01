<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\User;
use App\Models\Horario;
use App\Models\Consultorio;
use App\Models\Clinica;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $query = Cita::with(['doctor', 'paciente', 'consultorio', 'clinica']);

        if ($user->hasRole('doctor')) {
            $query->where('doctor_id', $user->id);
        }

        $citas = $query->latest()->paginate(10);
        return view('admin.citas.index', compact('citas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $doctor = null;
        if (auth()->user()->hasRole('doctor')) {
            $doctor = auth()->user()->load(['consultorios', 'clinicas']);
        }
        return view('admin.citas.create', compact('doctor'));
    }

    /**
     * Store a newly created resource in storage.
     */
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

        // Calculate end time based on schedule duration
        // We need to fetch the schedule again or pass duration, but better fetch safe.
        $dayOfWeek = Carbon::parse($validated['fecha'])->dayOfWeek;
        $horario = Horario::where('user_id', $validated['doctor_id'])
            ->where('consultorio_id', $validated['consultorio_id'])
            ->where('dia', $dayOfWeek)
            ->first();

        if (!$horario) {
             return back()->withErrors(['hora_inicio' => 'No hay horario disponible para este médico en esta fecha/consultorio.']);
        }

        $start = Carbon::createFromFormat('H:i', $validated['hora_inicio']);
        $end = $start->copy()->addMinutes($horario->duracion_minutos);

        $validated['hora_fin'] = $end->format('H:i');
        $validated['estado'] = 'pendiente';

        Cita::create($validated);

        return redirect()->route('citas.index')->with('success', 'Cita creada correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cita $cita)
    {
        if (auth()->user()->hasRole('doctor') && $cita->doctor_id !== auth()->id()) {
            abort(403, 'No tiene permiso para editar esta cita.');
        }
        return view('admin.citas.edit', compact('cita'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cita $cita)
    {
        if (auth()->user()->hasRole('doctor') && $cita->doctor_id !== auth()->id()) {
            abort(403, 'No tiene permiso para actualizar esta cita.');
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

        // If time/date/doctor changed, we need to validate availability again
        // But for now let's assume if they change it, they picked a valid slot from the UI.
        // We should ideally check if the slot is free (excluding this cita).
        
        $dayOfWeek = Carbon::parse($validated['fecha'])->dayOfWeek;
        $horario = Horario::where('user_id', $validated['doctor_id'])
            ->where('consultorio_id', $validated['consultorio_id'])
            ->where('dia', $dayOfWeek)
            ->first();

        if (!$horario) {
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
        if (auth()->user()->hasRole('doctor') && $cita->doctor_id !== auth()->id()) {
            abort(403, 'No tiene permiso para eliminar esta cita.');
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
            ->where(function($q) use ($search) {
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
        $user = auth()->user();

        $query = User::role('paciente')
            ->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });

        if ($user->hasRole('doctor')) {
            $query->whereHas('doctors', function($q) use ($user) {
                $q->where('users.id', $user->id);
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
        $doctor = User::with(['consultorios', 'clinicas'])->findOrFail($doctorId);
        
        // Return consultorios and clinicas
        // Note: Logic might need refinement if Consultorios are linked to Clinicas specifically.
        // Assuming independent many-to-many relationships as per User model.
        
        return response()->json([
            'consultorios' => $doctor->consultorios,
            'clinicas' => $doctor->clinicas
        ]);
    }

    /**
     * Get available slots.
     */
    public function getSlots(Request $request)
    {
        try {
            $request->validate([
                'doctor_id' => 'required|exists:users,id',
                'consultorio_id' => 'required|exists:consultorios,id',
                'fecha' => 'required|date',
            ]);

            $doctorId = $request->doctor_id;
            $consultorioId = $request->consultorio_id;
            $date = Carbon::parse($request->fecha);
            $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) - 6 (Saturday)

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
                'horarios_details' => $horarios->toArray()
            ];

            if ($horarios->isEmpty()) {
                return response()->json([
                    'slots' => [], 
                    'message' => 'El médico no tiene horario este día en este consultorio.',
                    'debug' => $debug
                ]);
            }

            $slots = [
                'Mañana' => [],
                'Tarde' => [],
                'Noche' => []
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
                ->map(function($time) {
                    return Carbon::parse($time)->format('H:i');
                })
                ->toArray();

            foreach ($horarios as $horario) {
                // Generate slots for each schedule block
                $startTime = Carbon::parse($horario->hora_inicio);
                $endTime = Carbon::parse($horario->hora_fin);
                $duration = $horario->duracion_minutos;

                if ($duration <= 0) continue; // Safety check

                $current = $startTime->copy();

                while ($current->copy()->addMinutes($duration)->lte($endTime)) {
                    $timeString = $current->format('H:i');
                    
                    // Check if slot is taken
                    if (!in_array($timeString, $existingCitas)) {
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
                'message' => 'Error: ' . $e->getMessage(),
                'debug' => ['exception' => $e->getTraceAsString()]
            ], 500);
        }
    }
}
