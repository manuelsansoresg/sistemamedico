<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuscripcionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener suscripciones con sus relaciones
        $suscripciones = Suscripcion::with(['user', 'paquete'])
            ->latest()
            ->paginate(15);
            
        return view('admin.suscripciones.index', compact('suscripciones'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Suscripcion $suscripcion)
    {
        $suscripcion->load(['user', 'paquete']);
        return view('admin.suscripciones.show', compact('suscripcion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Suscripcion $suscripcion)
    {
        $request->validate([
            'estatus_pago' => 'required|in:pendiente,pagado,fallido,vencido',
        ]);

        $suscripcion->update([
            'estatus_pago' => $request->estatus_pago
        ]);

        // Si se marca como pagado, podríamos activar al usuario si estaba inactivo por pago
        // O enviar notificación
        
        return redirect()->back()->with('success', 'Estatus de suscripción actualizado correctamente.');
    }

    /**
     * Validar Cédula de un Doctor
     */
    public function validarCedula(Request $request, User $user)
    {
        if (!$user->hasRole('doctor')) {
            return back()->with('error', 'El usuario no es un doctor.');
        }

        $request->validate([
            'accion' => 'required|in:validar,rechazar',
        ]);

        if ($request->accion === 'validar') {
            $user->update([
                'estatus_cedula' => 'validada',
                'cedula_validada_at' => now(),
            ]);
            $mensaje = 'Cédula validada correctamente. El doctor ya tiene acceso al panel.';
        } else {
            $user->update([
                'estatus_cedula' => 'rechazada',
            ]);
            $mensaje = 'Cédula rechazada.';
        }

        return back()->with('success', $mensaje);
    }
    
    /**
     * Descargar comprobante
     */
    public function downloadComprobante(Suscripcion $suscripcion)
    {
        if (!$suscripcion->comprobante_pago) {
            return back()->with('error', 'No hay comprobante disponible.');
        }
        
        return Storage::disk('public')->download($suscripcion->comprobante_pago);
    }
}
