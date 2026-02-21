<?php

namespace App\Http\Controllers;

use App\Models\Consultorio;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HorarioController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // If user is doctor or assistant/secretary, only show their owner's profile/consultorios
        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $query = User::where('id', $ownerId)->with('consultorios');
        } else {
            // Show selection form for Root
            // We need users who have 'doctor' role (or any user assigned to consultorios)
            // And consultorios.
            $query = User::whereHas('consultorios')->with('consultorios');

            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('apellido_paterno', 'like', "%{$search}%")
                        ->orWhere('apellido_materno', 'like', "%{$search}%");
                });
            }
        }

        // For simplicity, get all users with role 'doctor' or 'root'.
        // Or better, get users who have consultorios assigned.
        $users = $query->paginate(9)->withQueryString();

        // Also get all consultorios to map if needed, but user->consultorios is better context.

        return view('admin.horarios.index', compact('users'));
    }

    public function manage(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'consultorio_id' => 'required|exists:consultorios,id',
        ]);

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // Security check for doctors and assistants
        if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $currentUser->hasRole('doctor') ? $currentUser->id : $currentUser->created_by;

            // Can only manage the owner's schedule
            if ($request->user_id != $ownerId) {
                abort(403, 'No tiene permiso para gestionar los horarios de otro médico.');
            }
        }

        $user = User::findOrFail($request->user_id);
        $consultorio = Consultorio::findOrFail($request->consultorio_id);

        // Check if user is assigned to this consultorio
        if (! $user->consultorios->contains($consultorio->id)) {
            return redirect()->route('horarios.index')->with('error', 'El usuario no está asignado a este consultorio.');
        }

        $horariosCollection = Horario::where('user_id', $user->id)
            ->where('consultorio_id', $consultorio->id)
            ->orderBy('dia')
            ->orderBy('hora_inicio')
            ->get();

        $horarios = $horariosCollection->groupBy('dia');
        $duracionConsulta = $horariosCollection->first()->duracion_minutos ?? 30;

        return view('admin.horarios.manage', compact('user', 'consultorio', 'horarios', 'duracionConsulta'));
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Store Horarios Request:', $request->all());

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'consultorio_id' => 'required|exists:consultorios,id',
            'horarios' => 'array', // horarios[dia][] = ['inicio' => 'HH:MM', 'fin' => 'HH:MM']
            'duracion_consulta' => 'required|integer|in:20,30,45,60',
            'copiar_desde_consultorio_id' => 'nullable|exists:consultorios,id',
        ]);

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // Security check for doctors and assistants
        if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $currentUser->hasRole('doctor') ? $currentUser->id : $currentUser->created_by;

            // Can only manage the owner's schedule
            if ($request->user_id != $ownerId) {
                abort(403, 'No tiene permiso para gestionar los horarios de otro médico.');
            }
        }

        $userId = $request->user_id;
        $consultorioId = $request->consultorio_id;
        $duracion = $request->duracion_consulta;

        Horario::where('user_id', $userId)
            ->where('consultorio_id', $consultorioId)
            ->delete();

        if ($request->filled('copiar_desde_consultorio_id')) {
            $sourceConsultorioId = $request->copiar_desde_consultorio_id;

            $sourceHorarios = Horario::where('user_id', $userId)
                ->where('consultorio_id', $sourceConsultorioId)
                ->get();

            foreach ($sourceHorarios as $horario) {
                Horario::create([
                    'user_id' => $userId,
                    'consultorio_id' => $consultorioId,
                    'dia' => $horario->dia,
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                    'duracion_minutos' => $duracion,
                ]);
            }
        } else {
            if ($request->has('horarios')) {
                foreach ($request->horarios as $dia => $rangos) {
                    foreach ($rangos as $rango) {
                        if (! empty($rango['inicio']) && ! empty($rango['fin'])) {
                            if ($rango['inicio'] >= $rango['fin']) {
                                continue;
                            }

                            Horario::create([
                                'user_id' => $userId,
                                'consultorio_id' => $consultorioId,
                                'dia' => $dia,
                                'hora_inicio' => $rango['inicio'],
                                'hora_fin' => $rango['fin'],
                                'duracion_minutos' => $duracion,
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()->route('horarios.manage', ['user_id' => $userId, 'consultorio_id' => $consultorioId])
            ->with('success', 'Horarios actualizados exitosamente.');
    }
}
