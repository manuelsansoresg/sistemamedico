<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $catalogos = Catalogo::paginate(10);

        return view('admin.catalogos.index', compact('catalogos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.catalogos.create');
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
            'descripcion' => 'nullable|string',
        ]);

        Catalogo::create([
            'user_id' => Auth::id(),
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'porcentaje_ganancia' => $request->porcentaje_ganancia,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('catalogos.index')->with('success', __('catalogos.messages.created_success'));
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
    public function edit(Catalogo $catalogo)
    {
        return view('admin.catalogos.edit', compact('catalogo'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Catalogo $catalogo)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'precio' => 'required|numeric|min:0',
            'porcentaje_ganancia' => 'required|numeric|min:0|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $catalogo->update([
            'nombre' => $request->nombre,
            'precio' => $request->precio,
            'porcentaje_ganancia' => $request->porcentaje_ganancia,
            'descripcion' => $request->descripcion,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('catalogos.index')->with('success', __('catalogos.messages.updated_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Catalogo $catalogo)
    {
        $catalogo->delete();

        return redirect()->route('catalogos.index')->with('success', __('catalogos.messages.deleted_success'));
    }
}
