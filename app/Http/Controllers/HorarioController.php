<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\User;
use App\Models\Consultorio;
use Illuminate\Http\Request;

class HorarioController extends Controller
{
    public function index(Request $request)
    {
        // Show selection form
        // We need users who have 'doctor' role (or any user assigned to consultorios)
        // And consultorios.
        
        $query = User::whereHas('consultorios')->with('consultorios');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%");
            });
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

        $user = User::findOrFail($request->user_id);
        $consultorio = Consultorio::findOrFail($request->consultorio_id);

        // Check if user is assigned to this consultorio
        if (!$user->consultorios->contains($consultorio->id)) {
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
        ]);

        $userId = $request->user_id;
        $consultorioId = $request->consultorio_id;
        $duracion = $request->duracion_consulta;

        // Delete existing
        Horario::where('user_id', $userId)
               ->where('consultorio_id', $consultorioId)
               ->delete();

        if ($request->has('horarios')) {
            foreach ($request->horarios as $dia => $rangos) {
                foreach ($rangos as $rango) {
                    if (!empty($rango['inicio']) && !empty($rango['fin'])) {
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

        return redirect()->route('horarios.manage', ['user_id' => $userId, 'consultorio_id' => $consultorioId])
                         ->with('success', 'Horarios actualizados exitosamente.');
    }
}
