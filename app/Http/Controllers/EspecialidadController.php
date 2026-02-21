<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $especialidades = Especialidad::paginate(10);

        return view('admin.especialidades.index', compact('especialidades'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.especialidades.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:especialidades',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        Especialidad::create([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('especialidades.index')->with('success', 'Especialidad creada exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Especialidad $especialidad)
    {
        return view('admin.especialidades.edit', compact('especialidad'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Especialidad $especialidad)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:especialidades,nombre,'.$especialidad->id,
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $especialidad->update([
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('especialidades.index')->with('success', 'Especialidad actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Especialidad $especialidad)
    {
        $especialidad->delete();

        return redirect()->route('especialidades.index')->with('success', 'Especialidad eliminada exitosamente.');
    }
}
