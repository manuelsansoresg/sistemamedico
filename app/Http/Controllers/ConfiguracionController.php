<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConfiguracionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $configuraciones = Configuracion::with(['user', 'creator'])->latest()->paginate(10);

        return view('admin.configuraciones.index', compact('configuraciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();

        return view('admin.configuraciones.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:configuracions,user_id',
            'aceptar_transferencia_bancaria' => 'nullable|boolean',
            'banco' => 'nullable|required_if:aceptar_transferencia_bancaria,1|string|max:255',
            'titular' => 'nullable|required_if:aceptar_transferencia_bancaria,1|string|max:255',
            'cuenta' => 'nullable|required_if:aceptar_transferencia_bancaria,1|string|max:255',
            'clabe' => 'nullable|required_if:aceptar_transferencia_bancaria,1|string|max:255',
            'aceptar_pagos_con_tarjeta' => 'nullable|boolean',
        ]);

        Configuracion::create([
            'user_id' => $request->user_id,
            'created_by' => Auth::id(),
            'aceptar_transferencia_bancaria' => $request->has('aceptar_transferencia_bancaria'),
            'banco' => $request->banco,
            'titular' => $request->titular,
            'cuenta' => $request->cuenta,
            'clabe' => $request->clabe,
            'aceptar_pagos_con_tarjeta' => $request->has('aceptar_pagos_con_tarjeta'),
        ]);

        return redirect()->route('configuraciones.index')->with('success', __('configuraciones.messages.created_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Configuracion $configuracion)
    {
        $users = User::all();

        return view('admin.configuraciones.edit', compact('configuracion', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Configuracion $configuracion)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:configuracions,user_id,'.$configuracion->id,
            'aceptar_transferencia_bancaria' => 'nullable|boolean',
            'banco' => 'nullable|required_if:aceptar_transferencia_bancaria,1|string|max:255',
            'titular' => 'nullable|required_if:aceptar_transferencia_bancaria,1|string|max:255',
            'cuenta' => 'nullable|required_if:aceptar_transferencia_bancaria,1|string|max:255',
            'clabe' => 'nullable|required_if:aceptar_transferencia_bancaria,1|string|max:255',
            'aceptar_pagos_con_tarjeta' => 'nullable|boolean',
        ]);

        $configuracion->update([
            'user_id' => $request->user_id,
            'aceptar_transferencia_bancaria' => $request->has('aceptar_transferencia_bancaria'),
            'banco' => $request->banco,
            'titular' => $request->titular,
            'cuenta' => $request->cuenta,
            'clabe' => $request->clabe,
            'aceptar_pagos_con_tarjeta' => $request->has('aceptar_pagos_con_tarjeta'),
        ]);

        return redirect()->route('configuraciones.index')->with('success', __('configuraciones.messages.updated_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Configuracion $configuracion)
    {
        $configuracion->delete();

        return redirect()->route('configuraciones.index')->with('success', __('configuraciones.messages.deleted_success'));
    }
}
