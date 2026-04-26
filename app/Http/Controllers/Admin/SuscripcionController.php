<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Ganancia;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

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
                ->where(function ($q) {
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
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('comprobantes'), $filename);
            $comprobantePath = 'comprobantes/'.$filename;
        }

        $suscripcion = Suscripcion::create([
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
            'fecha_fin' => $tipo === 'paquete' ? now()->addYear() : null,
        ]);

        $admin = Auth::user();
        if ($admin instanceof User) {
            try {
                AuditLog::create([
                    'user_id' => $admin->id,
                    'action' => 'crear_suscripcion_admin',
                    'section' => 'suscripciones',
                    'model_type' => get_class($suscripcion),
                    'model_id' => $suscripcion->id,
                    'payload' => [
                        'tipo' => $tipo,
                        'paquete_id' => $paqueteId,
                        'catalogo_id' => $catalogoId,
                        'cantidad' => (int) $cantidad,
                        'precio' => (float) $price,
                        'metodo_pago' => $request->metodo_pago,
                        'estatus_pago' => $suscripcion->estatus_pago,
                        'comprobante_pago' => $comprobantePath,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                report($e);
            }
        }

        // Registrar ganancia si el pago es exitoso
        if ($suscripcion->estatus_pago === 'pagado') {
            $item = null;
            $type = null;

            if ($suscripcion->catalogo_id) {
                $item = $suscripcion->catalogo;
                $type = 'catalogo';
            } elseif ($suscripcion->paquete_id) {
                $item = $suscripcion->paquete;
                $type = 'paquete';
            }

            if ($item) {
                $montoGananciaDoctor = 0;
                $porcentajeAplicado = 0;

                // Lógica de cálculo según reglas:
                // 1. Paquetes: 100% ganancia para Root (Doctor = 0)
                if ($type === 'paquete') {
                    $montoGananciaDoctor = 0;
                    $porcentajeAplicado = 0;
                }
                // 2. Catálogos:
                else {
                    $porcentaje = $item->porcentaje_ganancia ?? 0;

                    if ($porcentaje == 0) {
                        // Si es 0%, Root se lleva el 100% (Doctor = 0)
                        $montoGananciaDoctor = 0;
                        $porcentajeAplicado = 0;
                    } else {
                        // Si es X% (ej: 5%), Doctor se lleva X%
                        $montoGananciaDoctor = $suscripcion->precio * ($porcentaje / 100);
                        $porcentajeAplicado = $porcentaje;
                    }
                }

                Ganancia::create([
                    'user_id' => $suscripcion->user_id,
                    'suscripcion_id' => $suscripcion->id,
                    'catalogo_id' => ($type === 'catalogo') ? $item->id : null,
                    'paquete_id' => ($type === 'paquete') ? $item->id : null,
                    'monto_total' => $suscripcion->precio,
                    'monto_ganancia_doctor' => $montoGananciaDoctor,
                    'porcentaje_aplicado' => $porcentajeAplicado,
                    'concepto' => 'Ganancia por adquisición de: '.$item->nombre,
                    'fecha' => now(),
                ]);
            }
        }

        return redirect()->route('admin.suscripciones.index')->with('success', 'Suscripción creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Suscripcion $suscripcion)
    {
        $suscripcion->load(['user', 'paquete']);
        $users = User::role('doctor')->get();

        return view('admin.suscripciones.show', compact('suscripcion', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Suscripcion $suscripcion)
    {
        $request->validate([
            'estatus_pago' => 'nullable|in:pendiente,pagado,rechazado,cancelado',
            'user_activo' => 'nullable|boolean',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $admin = Auth::user();
        $before = [
            'user_id' => $suscripcion->user_id,
            'estatus_pago' => $suscripcion->estatus_pago,
            'fecha_inicio' => optional($suscripcion->fecha_inicio)->toISOString(),
            'fecha_fin' => optional($suscripcion->fecha_fin)->toISOString(),
        ];

        // Actualizar usuario asignado si se envía
        if ($request->filled('user_id')) {
            $suscripcion->update([
                'user_id' => $request->user_id,
            ]);
        }

        // Actualizar estatus de pago si se envía
        if ($request->filled('estatus_pago')) {
            $suscripcion->update([
                'estatus_pago' => $request->estatus_pago,
            ]);

            // Si se marca como pagado, asegurar que las fechas sean válidas (si es necesario)
            if ($request->estatus_pago === 'pagado' && ! $suscripcion->fecha_inicio) {
                $suscripcion->update([
                    'fecha_inicio' => now(),
                    'fecha_fin' => now()->addYear(),
                ]);
            }

            // Verificar si hay ganancia pendiente de registrar al marcar como pagado
            if ($request->estatus_pago === 'pagado' && ! $suscripcion->ganancia) {
                $item = null;
                $type = null;

                if ($suscripcion->catalogo_id) {
                    $item = $suscripcion->catalogo;
                    $type = 'catalogo';
                } elseif ($suscripcion->paquete_id) {
                    $item = $suscripcion->paquete;
                    $type = 'paquete';
                }

                if ($item) {
                    $montoGananciaDoctor = 0;
                    $porcentajeAplicado = 0;

                    // Lógica de cálculo según reglas:
                    // 1. Paquetes: 100% ganancia para Root (Doctor = 0)
                    if ($type === 'paquete') {
                        $montoGananciaDoctor = 0;
                        $porcentajeAplicado = 0;
                    }
                    // 2. Catálogos:
                    else {
                        $porcentaje = $item->porcentaje_ganancia ?? 0;

                        if ($porcentaje == 0) {
                            // Si es 0%, Root se lleva el 100% (Doctor = 0)
                            $montoGananciaDoctor = 0;
                            $porcentajeAplicado = 0;
                        } else {
                            // Si es X% (ej: 5%), Doctor se lleva X%
                            $montoGananciaDoctor = $suscripcion->precio * ($porcentaje / 100);
                            $porcentajeAplicado = $porcentaje;
                        }
                    }

                    Ganancia::create([
                        'user_id' => $suscripcion->user_id,
                        'suscripcion_id' => $suscripcion->id,
                        'catalogo_id' => ($type === 'catalogo') ? $item->id : null,
                        'paquete_id' => ($type === 'paquete') ? $item->id : null,
                        'monto_total' => $suscripcion->precio,
                        'monto_ganancia_doctor' => $montoGananciaDoctor,
                        'porcentaje_aplicado' => $porcentajeAplicado,
                        'concepto' => 'Ganancia por adquisición de: '.$item->nombre,
                        'fecha' => now(),
                    ]);
                }
            }
        }

        // Actualizar acceso del usuario si se envía
        if ($request->has('user_activo')) {
            $suscripcion->user->update([
                'activo' => $request->user_activo,
            ]);
        }

        if ($admin instanceof User) {
            $after = [
                'user_id' => $suscripcion->fresh()->user_id,
                'estatus_pago' => $suscripcion->fresh()->estatus_pago,
                'fecha_inicio' => optional($suscripcion->fresh()->fecha_inicio)->toISOString(),
                'fecha_fin' => optional($suscripcion->fresh()->fecha_fin)->toISOString(),
                'user_activo' => $request->has('user_activo') ? (bool) $request->user_activo : null,
            ];

            $action = 'actualizar_suscripcion';
            if ($before['estatus_pago'] !== $after['estatus_pago']) {
                if ($after['estatus_pago'] === 'pagado') {
                    $action = 'aprobar_pago';
                } elseif ($after['estatus_pago'] === 'rechazado') {
                    $action = 'rechazar_pago';
                } elseif ($after['estatus_pago'] === 'cancelado') {
                    $action = 'cancelar_suscripcion';
                } elseif ($after['estatus_pago'] === 'pendiente') {
                    $action = 'marcar_pendiente';
                }
            }

            try {
                AuditLog::create([
                    'user_id' => $admin->id,
                    'action' => $action,
                    'section' => 'suscripciones',
                    'model_type' => get_class($suscripcion),
                    'model_id' => $suscripcion->id,
                    'payload' => [
                        'old' => $before,
                        'new' => $after,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return redirect()->back()->with('success', 'Suscripción actualizada correctamente.');
    }

    /**
     * Validar Cédula de un Doctor
     */
    public function validarCedula(Request $request, User $user)
    {
        if (! $user->hasRole('doctor')) {
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

        $admin = Auth::user();
        if ($admin instanceof User) {
            try {
                AuditLog::create([
                    'user_id' => $admin->id,
                    'action' => $request->accion === 'validar' ? 'validar_cedula' : 'rechazar_cedula',
                    'section' => 'seguridad',
                    'model_type' => get_class($user),
                    'model_id' => $user->id,
                    'payload' => null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return back()->with('success', $mensaje);
    }

    /**
     * Descargar comprobante
     */
    public function downloadComprobante(Suscripcion $suscripcion)
    {
        if (! $suscripcion->comprobante_pago) {
            return back()->with('error', 'No hay comprobante disponible.');
        }

        // La ruta guardada es relativa a public (ej: "comprobantes/archivo.jpg")
        $path = public_path($suscripcion->comprobante_pago);
        $downloadPath = null;

        if (file_exists($path)) {
            $downloadPath = $path;
        } else {
            // Intentar con storage path por si acaso (compatibilidad anterior)
            if (Storage::disk('public')->exists($suscripcion->comprobante_pago)) {
                $downloadPath = Storage::disk('public')->path($suscripcion->comprobante_pago);
            }

            if (! $downloadPath) {
                return back()->with('error', 'El archivo no existe.');
            }
        }

        $admin = Auth::user();
        if ($admin instanceof User) {
            try {
                AuditLog::create([
                    'user_id' => $admin->id,
                    'action' => 'descargar_comprobante',
                    'section' => 'suscripciones',
                    'model_type' => get_class($suscripcion),
                    'model_id' => $suscripcion->id,
                    'payload' => [
                        'archivo' => $suscripcion->comprobante_pago,
                    ],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return response()->download($downloadPath);
    }
}
