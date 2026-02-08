<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Suscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompraController extends Controller
{
    public function index()
    {
        $catalogos = Catalogo::where('activo', true)->get();
        $suscripciones = Suscripcion::where('user_id', Auth::id())
            ->with(['paquete', 'catalogo'])
            ->latest()
            ->get();
            
        return view('compras.index', compact('catalogos', 'suscripciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'catalogo_id' => 'required|exists:catalogos,id',
            'cantidad' => 'required|integer|min:1',
            'metodo_pago' => 'required|in:tarjeta,transferencia',
        ]);

        $item = Catalogo::findOrFail($request->catalogo_id);
        $cantidad = $request->cantidad;
        $precioTotal = $item->precio * $cantidad;

        // Determinar estatus y fechas según método de pago
        $estatusPago = 'pendiente';
        $fechaInicio = null;
        
        // Si es tarjeta, se asume pago exitoso inmediato (simulación o integración futura)
        if ($request->metodo_pago === 'tarjeta') {
            $estatusPago = 'pagado';
            $fechaInicio = now();
        }

        Suscripcion::create([
            'user_id' => Auth::id(),
            'paquete_id' => null,
            'catalogo_id' => $item->id,
            'cantidad' => $cantidad,
            'tipo' => 'individual',
            'precio' => $precioTotal,
            'metodo_pago' => $request->metodo_pago,
            'estatus_pago' => $estatusPago,
            'comprobante_pago' => null, // Se subirá después si es transferencia
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => null, 
        ]);

        $mensaje = ($estatusPago === 'pagado') 
            ? 'Compra realizada exitosamente.' 
            : 'Solicitud generada. Por favor sube tu comprobante de pago para activar el servicio.';

        return redirect()->route('compras.index')->with('success', $mensaje);
    }

    public function uploadComprobante(Request $request, Suscripcion $suscripcion)
    {
        if ($suscripcion->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'comprobante_pago' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('comprobante_pago')) {
             $file = $request->file('comprobante_pago');
             $filename = time() . '_' . $file->getClientOriginalName();
             $file->move(public_path('comprobantes'), $filename);
             
             $suscripcion->update([
                 'comprobante_pago' => 'comprobantes/' . $filename,
                 // Mantenemos estatus 'pendiente' hasta que admin valide
             ]);
        }

        return back()->with('success', 'Comprobante subido correctamente. Esperando validación del administrador.');
    }
}
