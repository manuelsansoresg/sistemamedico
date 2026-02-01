<?php

namespace App\Http\Controllers;

use App\Models\Plantilla;
use App\Models\PlantillaCampo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PlantillaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Plantilla::with(['user', 'creator']);

        if ($user->hasRole('doctor')) {
            $query->where('user_id', $user->id);
        }

        $plantillas = $query->paginate(10);
        return view('admin.plantillas.index', compact('plantillas'));
    }

    public function create()
    {
        $doctors = null;
        if (Auth::user()->hasRole('root')) {
            $doctors = User::role('doctor')->get();
        }

        return view('admin.plantillas.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id', // Required if root
            'campos' => 'required|array|min:1',
            'campos.*.nombre' => 'required|string|max:255',
            'campos.*.tipo' => 'required|in:text,date,textarea,select',
            'campos.*.es_obligatorio' => 'boolean',
            'campos.*.opciones' => 'nullable|string', // Comma separated or JSON string from frontend
        ]);

        if (Auth::user()->hasRole('root') && !$request->user_id) {
            return back()->withErrors(['user_id' => 'Debe seleccionar un doctor.'])->withInput();
        }

        $userId = Auth::user()->hasRole('root') ? $request->user_id : Auth::id();

        DB::transaction(function () use ($request, $userId) {
            $plantilla = Plantilla::create([
                'nombre' => $request->nombre,
                'user_id' => $userId,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->campos as $index => $campoData) {
                // Process options if select
                $opciones = null;
                if ($campoData['tipo'] === 'select' && !empty($campoData['opciones'])) {
                    // Expecting options as comma separated string or array?
                    // Let's assume the frontend sends a string or array.
                    // If string "A,B,C", split it.
                    if (is_string($campoData['opciones'])) {
                        $opciones = array_map('trim', explode(',', $campoData['opciones']));
                    } else {
                        $opciones = $campoData['opciones'];
                    }
                }

                PlantillaCampo::create([
                    'plantilla_id' => $plantilla->id,
                    'nombre' => $campoData['nombre'],
                    'slug' => Str::slug($campoData['nombre']),
                    'tipo' => $campoData['tipo'],
                    'es_obligatorio' => isset($campoData['es_obligatorio']) ? $campoData['es_obligatorio'] : false,
                    'opciones' => $opciones,
                    'orden' => $index,
                ]);
            }
        });

        return redirect()->route('plantillas.index')->with('success', 'Plantilla creada exitosamente.');
    }

    public function edit(Plantilla $plantilla)
    {
        // Authorization check
        if (Auth::user()->hasRole('doctor') && $plantilla->user_id !== Auth::id()) {
            abort(403);
        }

        $doctors = null;
        if (Auth::user()->hasRole('root')) {
            $doctors = User::role('doctor')->get();
        }

        $plantilla->load('campos');

        return view('admin.plantillas.edit', compact('plantilla', 'doctors'));
    }

    public function update(Request $request, Plantilla $plantilla)
    {
        // Authorization check
        if (Auth::user()->hasRole('doctor') && $plantilla->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'campos' => 'required|array|min:1',
            'campos.*.nombre' => 'required|string|max:255',
            'campos.*.tipo' => 'required|in:text,date,textarea,select',
            'campos.*.es_obligatorio' => 'boolean',
            'campos.*.opciones' => 'nullable|string',
        ]);

        if (Auth::user()->hasRole('root') && !$request->user_id) {
            return back()->withErrors(['user_id' => 'Debe seleccionar un doctor.'])->withInput();
        }

        DB::transaction(function () use ($request, $plantilla) {
            $plantilla->update([
                'nombre' => $request->nombre,
                'user_id' => Auth::user()->hasRole('root') ? $request->user_id : $plantilla->user_id,
            ]);

            // Replace fields strategy: Delete all and recreate.
            // Or try to sync? Deleting and recreating is easier for ordering and structure changes.
            // However, if we had data in consultations linked to these fields, we'd have a problem.
            // Since this is just a template definition, it might be okay.
            // But if there are consultations using this template, we shouldn't delete fields blindly.
            // The prompt doesn't mention consultations yet, just "iniciar una consulta".
            // Let's assume for now we can wipe and recreate fields for the template.
            
            $plantilla->campos()->delete();

            foreach ($request->campos as $index => $campoData) {
                $opciones = null;
                if ($campoData['tipo'] === 'select' && !empty($campoData['opciones'])) {
                    if (is_string($campoData['opciones'])) {
                        $opciones = array_map('trim', explode(',', $campoData['opciones']));
                    } else {
                        $opciones = $campoData['opciones'];
                    }
                }

                PlantillaCampo::create([
                    'plantilla_id' => $plantilla->id,
                    'nombre' => $campoData['nombre'],
                    'slug' => Str::slug($campoData['nombre']),
                    'tipo' => $campoData['tipo'],
                    'es_obligatorio' => isset($campoData['es_obligatorio']) ? $campoData['es_obligatorio'] : false,
                    'opciones' => $opciones,
                    'orden' => $index,
                ]);
            }
        });

        return redirect()->route('plantillas.index')->with('success', 'Plantilla actualizada exitosamente.');
    }

    public function destroy(Plantilla $plantilla)
    {
        if (Auth::user()->hasRole('doctor') && $plantilla->user_id !== Auth::id()) {
            abort(403);
        }

        $plantilla->delete();
        return redirect()->route('plantillas.index')->with('success', 'Plantilla eliminada exitosamente.');
    }

    public function getCampos(Plantilla $plantilla)
    {
        // Allow if root or if doctor owns it
        if (Auth::user()->hasRole('doctor') && $plantilla->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $campos = $plantilla->campos()->orderBy('orden')->get()->map(function($campo) {
            return [
                'id' => $campo->id,
                'etiqueta' => $campo->nombre,
                'tipo' => $campo->tipo,
                'opciones' => is_array($campo->opciones) ? implode(',', $campo->opciones) : $campo->opciones,
                'required' => $campo->es_obligatorio
            ];
        });

        return response()->json($campos);
    }
}
