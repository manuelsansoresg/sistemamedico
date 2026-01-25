<?php

namespace App\Http\Controllers;

use App\Models\Consultorio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $consultorios = Consultorio::with('creator')->latest()->paginate(10);
        return view('admin.consultorios.index', compact('consultorios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.consultorios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'activo' => 'boolean',
        ]);

        Consultorio::create([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'activo' => $request->has('activo'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('consultorios.index')->with('success', 'Consultorio creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Consultorio $consultorio)
    {
        return view('admin.consultorios.edit', compact('consultorio'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Consultorio $consultorio)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'activo' => 'boolean',
        ]);

        $consultorio->update([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('consultorios.index')->with('success', 'Consultorio actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consultorio $consultorio)
    {
        $consultorio->delete();
        return redirect()->route('consultorios.index')->with('success', 'Consultorio eliminado exitosamente.');
    }
}
