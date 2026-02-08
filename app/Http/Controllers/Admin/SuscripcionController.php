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
    public function index(Request $request)
    {
        $query = Suscripcion::with(['user', 'paquete', 'catalogo']);

        // Filtro por búsqueda (Nombre o Email del usuario)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por rango de fechas
        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }
        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }

        // Ordenar por fecha (mayor a menor tiempo)
        $suscripciones = $query->latest()
            ->paginate(15)
            ->withQueryString(); // Mantener parámetros de filtros en paginación
            
        return view('admin.suscripciones.index', compact('suscripciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::role('doctor')->get();
        $paquetes = \App\Models\Paquete::where('activo', true)->get();
        $catalogos = \App\Models\Catalogo::where('activo', true)->get();
        
        return view('admin.suscripciones.create', compact('users', 'paquetes', 'catalogos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tipo' => 'required|in:paquete,individual',
            'item_id' => 'required',
            'cantidad' => 'required_if:tipo,individual|integer|min:1',
            'metodo_pago' => 'required|in:tarjeta,transferencia',
            'comprobante_pago' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $tipo = $request->tipo;
        $itemId = $request->item_id;
        $cantidad = ($tipo === 'individual') ? $request->cantidad : 1;
        $price = 0;
        $paqueteId = null;
        $catalogoId = null;

        if ($tipo === 'paquete') {
            // Verificar si el usuario ya tiene un paquete activo
            $activePackage = Suscripcion::where('user_id', $request->user_id)
                ->where('tipo', 'paquete')
                ->where('estatus_pago', 'pagado')
                ->where(function($q) {
                    $q->whereNull('fecha_fin')
                      ->orWhere('fecha_fin', '>', now());
                })
                ->exists();

            if ($activePackage) {
                return back()->with('error', 'El usuario ya tiene un paquete activo. Solo se permite un paquete activo a la vez.');
            }

            $item = \App\Models\Paquete::findOrFail($itemId);
            $price = $item->precio; // Paquetes son siempre cantidad 1
            $paqueteId = $itemId;
        } else {
            $item = \App\Models\Catalogo::findOrFail($itemId);
            $price = $item->precio * $cantidad;
            $catalogoId = $itemId;
        }

        $comprobantePath = null;
        if ($request->hasFile('comprobante_pago')) {
             $file = $request->file('comprobante_pago');
             $filename = time() . '_' . $file->getClientOriginalName();
             $file->move(public_path('comprobantes'), $filename);
             $comprobantePath = 'comprobantes/' . $filename;
        }

        Suscripcion::create([
            'user_id' => $request->user_id,
            'paquete_id' => $paqueteId,
            'catalogo_id' => $catalogoId,
            'cantidad' => $cantidad,
            'tipo' => $tipo,
            'precio' => $price,
            'metodo_pago' => $request->metodo_pago,
            'estatus_pago' => $request->metodo_pago === 'transferencia' ? 'pendiente' : 'pagado',
            'comprobante_pago' => $comprobantePath,
            'fecha_inicio' => now(),
            'fecha_fin' => $tipo === 'paquete' ? now()->addMonth() : null,
        ]);

        return redirect()->route('admin.suscripciones.index')->with('success', 'Suscripción creada correctamente.');
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
            'estatus_pago' => 'nullable|in:pendiente,pagado,fallido,vencido',
            'user_activo' => 'nullable|boolean',
        ]);

        // Actualizar estatus de pago si se envía
        if ($request->filled('estatus_pago')) {
            $suscripcion->update([
                'estatus_pago' => $request->estatus_pago
            ]);
            
            // Si se marca como pagado, asegurar que las fechas sean válidas (si es necesario)
            if ($request->estatus_pago === 'pagado' && !$suscripcion->fecha_inicio) {
                 $suscripcion->update([
                     'fecha_inicio' => now(),
                     'fecha_fin' => $suscripcion->tipo === 'paquete' ? now()->addMonth() : null,
                 ]);
            }
        }

        // Actualizar acceso del usuario si se envía
        if ($request->has('user_activo')) {
            $suscripcion->user->update([
                'activo' => $request->user_activo
            ]);
        }

        return redirect()->back()->with('success', 'Suscripción actualizada correctamente.');
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
        
        // La ruta guardada es relativa a public (ej: "comprobantes/archivo.jpg")
        $path = public_path($suscripcion->comprobante_pago);
        
        if (!file_exists($path)) {
            // Intentar con storage path por si acaso (compatibilidad anterior)
            if (Storage::disk('public')->exists($suscripcion->comprobante_pago)) {
                return Storage::disk('public')->download($suscripcion->comprobante_pago);
            }
            return back()->with('error', 'El archivo no existe.');
        }
        
        return response()->download($path);
    }
}
