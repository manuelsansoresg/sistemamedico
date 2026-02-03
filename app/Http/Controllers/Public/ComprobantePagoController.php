<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComprobantePagoController extends Controller
{
    public function show($token)
    {
        $suscripcion = Suscripcion::where('token_pago', $token)->firstOrFail();

        if ($suscripcion->estatus_pago === 'pagado') {
            return view('public.comprobante_pagado');
        }

        return view('public.subir_comprobante', compact('suscripcion', 'token'));
    }

    public function store(Request $request, $token)
    {
        $suscripcion = Suscripcion::where('token_pago', $token)->firstOrFail();

        $request->validate([
            'comprobante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('comprobante')) {
            $path = $request->file('comprobante')->store('comprobantes', 'public');
            
            $suscripcion->update([
                'comprobante_pago' => $path,
                // No cambiamos a 'pagado' automáticamente, el admin debe validar
                // Pero podríamos poner un estatus intermedio si existiera, o dejar en pendiente
            ]);

            return redirect()->back()->with('success', 'Comprobante subido exitosamente. Tu cuenta será activada una vez validemos el pago.');
        }

        return redirect()->back()->with('error', 'Hubo un problema al subir el archivo.');
    }
}
