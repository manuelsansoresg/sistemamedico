<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Catalogo;
use App\Models\Ganancia;
use App\Models\Paquete;
use App\Models\Suscripcion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CompraController extends Controller
{
    public function index()
    {
        $catalogos = Catalogo::where('activo', true)->get();
        $suscripciones = Suscripcion::where('user_id', Auth::id())
            ->with(['paquete.catalogos', 'catalogo'])
            ->withCount('pacientes')
            ->latest()
            ->paginate(10);

        $suscripcionPaqueteVencida = Suscripcion::where('user_id', Auth::id())
            ->where('tipo', 'paquete')
            ->where('estatus_pago', 'pagado')
            ->whereNotNull('fecha_fin')
            ->where('fecha_fin', '<', now())
            ->with('paquete')
            ->orderByDesc('fecha_fin')
            ->first();

        return view('compras.index', compact('catalogos', 'suscripciones', 'suscripcionPaqueteVencida'));
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

        $suscripcion = Suscripcion::create([
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
            'fecha_fin' => $estatusPago === 'pagado' ? now()->addYear() : null,
        ]);

        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'compra_catalogo',
                'section' => 'compras',
                'model_type' => get_class($suscripcion),
                'model_id' => $suscripcion->id,
                'payload' => [
                    'catalogo_id' => $item->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => (float) $item->precio,
                    'precio_total' => (float) $precioTotal,
                    'metodo_pago' => $request->metodo_pago,
                    'estatus_pago' => $estatusPago,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }

        // Si se paga con tarjeta, registrar ganancia inmediatamente
        if ($estatusPago === 'pagado') {
            $montoGanancia = 0;
            $porcentajeAplicado = 0;

            if ($item->porcentaje_ganancia > 0) {
                $montoGanancia = $precioTotal * ($item->porcentaje_ganancia / 100);
                $porcentajeAplicado = $item->porcentaje_ganancia;
            }

            Ganancia::create([
                'user_id' => Auth::id(),
                'suscripcion_id' => $suscripcion->id,
                'catalogo_id' => $item->id,
                'monto_total' => $precioTotal,
                'monto_ganancia_doctor' => $montoGanancia,
                'porcentaje_aplicado' => $porcentajeAplicado,
                'concepto' => 'Ganancia por adquisición de: '.$item->nombre,
                'fecha' => now(),
            ]);
        }

        $mensaje = ($estatusPago === 'pagado')
            ? 'Compra realizada exitosamente.'
            : 'Solicitud generada. Por favor sube tu comprobante de pago para activar el servicio.';

        return redirect()->route('compras.index')->with('success', $mensaje);
    }

    public function renewPackage(Request $request)
    {
        $request->validate([
            'suscripcion_anterior_id' => ['required', 'integer', 'exists:suscripciones,id'],
            'metodo_pago' => ['required', 'in:tarjeta,transferencia'],
        ]);

        $previous = Suscripcion::with('paquete')->findOrFail($request->suscripcion_anterior_id);

        if ($previous->user_id !== Auth::id() || $previous->tipo !== 'paquete' || ! $previous->paquete_id) {
            abort(403);
        }

        $paquete = $previous->paquete ?: Paquete::findOrFail($previous->paquete_id);

        $estatusPago = 'pendiente';
        $fechaInicio = null;

        if ($request->metodo_pago === 'tarjeta') {
            $estatusPago = 'pagado';
            $fechaInicio = now();
        }

        $newSuscripcion = Suscripcion::create([
            'user_id' => Auth::id(),
            'paquete_id' => $paquete->id,
            'catalogo_id' => null,
            'cantidad' => 1,
            'tipo' => 'paquete',
            'precio' => $paquete->precio,
            'metodo_pago' => $request->metodo_pago,
            'estatus_pago' => $estatusPago,
            'comprobante_pago' => null,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => ($estatusPago === 'pagado') ? now()->addYear() : null,
        ]);

        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'renovar_paquete',
                'section' => 'compras',
                'model_type' => get_class($newSuscripcion),
                'model_id' => $newSuscripcion->id,
                'payload' => [
                    'suscripcion_anterior_id' => $previous->id,
                    'paquete_id' => $paquete->id,
                    'precio' => (float) $paquete->precio,
                    'metodo_pago' => $request->metodo_pago,
                    'estatus_pago' => $estatusPago,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }

        $mensaje = ($estatusPago === 'pagado')
            ? 'Renovación realizada exitosamente.'
            : 'Solicitud de renovación generada. Por favor sube tu comprobante de pago para activar el paquete.';

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
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('comprobantes'), $filename);

            $suscripcion->update([
                'comprobante_pago' => 'comprobantes/'.$filename,
                // Mantenemos estatus 'pendiente' hasta que admin valide
            ]);

            try {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'subir_comprobante',
                    'section' => 'suscripciones',
                    'model_type' => get_class($suscripcion),
                    'model_id' => $suscripcion->id,
                    'payload' => [
                        'archivo' => 'comprobantes/'.$filename,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', 'Comprobante subido correctamente. Esperando validación del administrador.');
    }
}
