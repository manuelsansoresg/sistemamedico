<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios = Servicio::paginate(15);

        return view('admin.servicios.index', compact('servicios'));
    }

    public function create()
    {
        return view('admin.servicios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'duracion' => 'required|integer|min:1',
            'costo' => 'required|numeric|min:0',
        ]);

        Servicio::create([
            'nombre' => $request->nombre,
            'duracion' => $request->duracion,
            'costo' => $request->costo,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('servicios.index')->with('success', __('servicios.messages.created_success'));
    }

    public function edit(Servicio $servicio)
    {
        return view('admin.servicios.edit', compact('servicio'));
    }

    public function update(Request $request, Servicio $servicio)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'duracion' => 'required|integer|min:1',
            'costo' => 'required|numeric|min:0',
        ]);

        $servicio->update([
            'nombre' => $request->nombre,
            'duracion' => $request->duracion,
            'costo' => $request->costo,
        ]);

        return redirect()->route('servicios.index')->with('success', __('servicios.messages.updated_success'));
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->route('servicios.index')->with('success', __('servicios.messages.deleted_success'));
    }
}
