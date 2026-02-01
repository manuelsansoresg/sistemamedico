<?php

namespace App\Http\Controllers;

use App\Models\Pendiente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pendientes = Pendiente::where('user_id', Auth::id())
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(10);
            
        return view('doctor.pendientes.index', compact('pendientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('doctor.pendientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recordatorio' => 'required|string',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'activo' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['activo'] = $request->has('activo') ? true : false; // Handle checkbox

        Pendiente::create($validated);

        return redirect()->route('pendientes.index')->with('success', 'Recordatorio creado correctamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pendiente $pendiente)
    {
        // Authorization check
        if ($pendiente->user_id !== Auth::id()) {
            abort(403);
        }

        return view('doctor.pendientes.edit', compact('pendiente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pendiente $pendiente)
    {
        // Authorization check
        if ($pendiente->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'recordatorio' => 'required|string',
            'fecha' => 'required|date',
            'hora' => 'required|date_format:H:i',
            'activo' => 'boolean',
        ]);
        
        // Handle checkbox logic manually if not present in request it's false, but validate boolean handles '1', 'true', 'on', etc.
        // If uncheck, it's missing from request usually.
        $validated['activo'] = $request->has('activo') ? true : false;

        $pendiente->update($validated);

        return redirect()->route('pendientes.index')->with('success', 'Recordatorio actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pendiente $pendiente)
    {
        // Authorization check
        if ($pendiente->user_id !== Auth::id()) {
            abort(403);
        }

        $pendiente->delete();

        return redirect()->route('pendientes.index')->with('success', 'Recordatorio eliminado correctamente.');
    }
}
