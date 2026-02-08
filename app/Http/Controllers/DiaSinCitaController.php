<?php

namespace App\Http\Controllers;

use App\Models\DiaSinCita;
use App\Models\Consultorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiaSinCitaController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = DiaSinCita::with(['consultorios', 'user']);

        if ($user->hasRole('doctor')) {
            // Doctor sees blocks affecting their consultorios
            $consultorioIds = Consultorio::where('created_by', $user->id)->pluck('id');
            $query->whereHas('consultorios', function($q) use ($consultorioIds) {
                $q->whereIn('consultorios.id', $consultorioIds);
            });
        }
        // Root sees all

        $diasSinCitas = $query->latest()->paginate(10);
        return view('dias_sin_citas.index', compact('diasSinCitas'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if ($user->hasRole('doctor')) {
            $consultorios = Consultorio::where('created_by', $user->id)->where('activo', true)->get();
        } else {
            $consultorios = Consultorio::where('activo', true)->get();
        }

        return view('dias_sin_citas.create', compact('consultorios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'consultorios' => 'required|array|min:1',
            'consultorios.*' => 'exists:consultorios,id',
            'motivo' => 'required|string|max:255',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'todo_el_dia' => 'boolean',
            'hora_inicio' => 'nullable|required_if:todo_el_dia,false|date_format:H:i',
            'hora_fin' => 'nullable|required_if:todo_el_dia,false|date_format:H:i|after:hora_inicio',
        ]);

        $diaSinCita = DiaSinCita::create([
            'user_id' => Auth::id(),
            'motivo' => $request->motivo,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'hora_inicio' => $request->boolean('todo_el_dia') ? null : $request->hora_inicio,
            'hora_fin' => $request->boolean('todo_el_dia') ? null : $request->hora_fin,
            'todo_el_dia' => $request->boolean('todo_el_dia'),
        ]);

        $diaSinCita->consultorios()->sync($request->consultorios);

        return redirect()->route('dias-sin-citas.index')->with('success', 'Día sin citas registrado exitosamente.');
    }

    public function destroy(DiaSinCita $diaSinCita)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('doctor') && $diaSinCita->user_id !== $user->id) {
            abort(403, 'No tienes permiso para eliminar este registro.');
        }

        $diaSinCita->delete();

        return redirect()->route('dias-sin-citas.index')->with('success', 'Registro eliminado exitosamente.');
    }
}
