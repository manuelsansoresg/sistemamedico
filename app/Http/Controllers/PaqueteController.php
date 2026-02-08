<?php

namespace App\Http\Controllers;

use App\Models\Paquete;
use App\Models\Catalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaqueteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paquetes = Paquete::with('catalogos')->paginate(10);
        return view('admin.paquetes.index', compact('paquetes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $catalogos = Catalogo::where('activo', true)->get();
        return view('admin.paquetes.create', compact('catalogos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'porcentaje_ganancia' => 'required|numeric|min:0|max:100',
            'tipo' => 'required|in:clinica,consultorio',
            'elementos' => 'required|array',
            'elementos.*.cantidad_maxima' => 'nullable|integer|min:0',
            'elementos.*.precio' => 'nullable|numeric|min:0',
        ]);

        $paquete = Paquete::create([
            'user_id' => Auth::id(),
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'porcentaje_ganancia' => $request->porcentaje_ganancia,
            'activo' => $request->has('activo'),
            'tipo' => $request->tipo,
            'validar_cedula' => $request->has('validar_cedula'),
        ]);

        // Sync items
        $syncData = [];
        if ($request->has('elementos')) {
            foreach ($request->elementos as $id => $data) {
                if (isset($data['checked'])) {
                    $syncData[$id] = [
                        'cantidad_maxima' => $data['cantidad_maxima'] ?? null,
                        'precio' => $data['precio'] ?? 0,
                    ];
                }
            }
        }
        $paquete->catalogos()->sync($syncData);

        return redirect()->route('paquetes.index')->with('success', 'Paquete creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Paquete $paquete)
    {
        $catalogos = Catalogo::where('activo', true)->get();
        return view('admin.paquetes.edit', compact('paquete', 'catalogos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Paquete $paquete)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'porcentaje_ganancia' => 'required|numeric|min:0|max:100',
            'tipo' => 'required|in:clinica,consultorio',
            'elementos' => 'nullable|array',
        ]);

        $paquete->update([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'porcentaje_ganancia' => $request->porcentaje_ganancia,
            'activo' => $request->has('activo'),
            'tipo' => $request->tipo,
            'validar_cedula' => $request->has('validar_cedula'),
        ]);

        // Sync items
        $syncData = [];
        if ($request->has('elementos')) {
            foreach ($request->elementos as $id => $data) {
                if (isset($data['checked'])) {
                    $syncData[$id] = [
                        'cantidad_maxima' => $data['cantidad_maxima'] ?? null,
                        'precio' => $data['precio'] ?? 0,
                    ];
                }
            }
        }
        $paquete->catalogos()->sync($syncData);

        return redirect()->route('paquetes.index')->with('success', 'Paquete actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paquete $paquete)
    {
        $paquete->delete();
        return redirect()->route('paquetes.index')->with('success', 'Paquete eliminado exitosamente.');
    }
}
