<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use Illuminate\Http\Request;

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
            'comprobante' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // Max 2MB
        ]);

        if ($request->hasFile('comprobante')) {
            $file = $request->file('comprobante');
            $filename = time().'_'.$file->getClientOriginalName();

            // Guardar directamente en public/comprobantes
            $file->move(public_path('comprobantes'), $filename);

            // Guardar la ruta relativa para acceso web
            $path = 'comprobantes/'.$filename;

            $suscripcion->update([
                'comprobante_pago' => $path,
            ]);

            return redirect()->route('suscripciones.comprobante_enviado', $token);
        }

        return redirect()->back()->with('error', 'Hubo un problema al subir el archivo.');
    }

    public function enviado($token)
    {
        // Validar que exista la suscripción
        $suscripcion = Suscripcion::where('token_pago', $token)->firstOrFail();

        return view('public.comprobante_enviado');
    }
}
