<?php

namespace App\Http\Controllers;

use App\Models\ArticuloCobro;
use App\Models\Cita;
use App\Models\CitaAfectacion;
use App\Models\ConsultaCobro;
use App\Models\ConsultaCobroItem;
use App\Models\User;
use App\Services\ConsultaCobroService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultaCobroController extends Controller
{
    public function __construct(private ConsultaCobroService $consultaCobroService) {}

    public function show(Cita $cita)
    {
        $this->authorizeCitaAccess($cita);

        $cita->load([
            'doctor',
            'paciente',
            'consultorio',
            'cobro.items',
            'cobro.afectaciones.citaAfectada.paciente',
        ]);

        $cobro = $cita->cobro;
        $articulos = ArticuloCobro::where('doctor_id', $cita->doctor_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        $articulosPayload = $this->formatArticulos($articulos);

        return view('admin.consulta_cobros.show', compact('cita', 'cobro', 'articulos', 'articulosPayload'));
    }

    public function status(Cita $cita)
    {
        $this->authorizeCitaAccess($cita);

        $cita->load(['cobro.items', 'cobro.afectaciones']);
        $cobro = $cita->cobro;

        $estadoInstrucciones = $cobro?->estado_instrucciones ?? 'pendiente';

        return response()->json([
            'estado_instrucciones' => trans_enum('cobros.statuses.'.$estadoInstrucciones, $estadoInstrucciones),
            'estado_cobro' => $cobro?->estado_cobro ?? 'pendiente',
            'total' => $cobro ? number_format((float) $cobro->total, 2) : '0.00',
            'items_count' => $cobro?->items->count() ?? 0,
            'affected_count' => $cobro?->afectaciones->count() ?? 0,
            'updated_at' => $cobro?->updated_at?->format('d/m/Y H:i:s'),
        ]);
    }

    public function articulos(Cita $cita)
    {
        $this->authorizeCitaAccess($cita);

        $articulos = ArticuloCobro::where('doctor_id', $cita->doctor_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'precio']);

        return response()->json($this->formatArticulos($articulos));
    }

    public function preview(Request $request, Cita $cita)
    {
        $this->authorizeDoctorAccess($cita);

        $validated = $request->validate([
            'servicios' => ['nullable', 'array'],
            'servicios.*.selected' => ['nullable'],
            'servicios.*.precio_cobrado' => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json($this->consultaCobroService->preview($cita, $validated['servicios'] ?? []));
    }

    public function saveDoctor(Request $request, Cita $cita)
    {
        $this->authorizeDoctorAccess($cita);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'estado_instrucciones' => ['required', 'in:sin_instrucciones,con_instrucciones'],
            'instrucciones_cobro' => ['nullable', 'string'],
            'servicios' => ['nullable', 'array'],
            'servicios.*.selected' => ['nullable'],
            'servicios.*.precio_cobrado' => ['nullable', 'numeric', 'min:0'],
            'servicios.*.motivo_ajuste' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['estado_instrucciones'] === 'con_instrucciones' && empty($validated['instrucciones_cobro']) && empty($validated['servicios'])) {
            return back()->withErrors(['instrucciones_cobro' => __('cobros.validation.instructions_or_services_required')]);
        }

        $this->consultaCobroService->saveDoctorCobro($cita, $user, $validated);

        return back()->with('success', __('cobros.messages.instructions_saved'));
    }

    public function storeArticuloItem(Request $request, ConsultaCobro $consultaCobro)
    {
        $this->authorizeCitaAccess($consultaCobro->cita);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'articulo_cobro_id' => ['required', 'exists:articulos_cobro,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'precio_cobrado' => ['required', 'numeric', 'min:0'],
            'motivo_ajuste' => ['nullable', 'string', 'max:255'],
        ]);

        $articulo = ArticuloCobro::where('doctor_id', $consultaCobro->doctor_id)
            ->whereKey($validated['articulo_cobro_id'])
            ->firstOrFail();

        $precioCatalogo = (float) $articulo->precio;
        $precioCobrado = round((float) $validated['precio_cobrado'], 2);
        $cantidad = (int) $validated['cantidad'];

        $consultaCobro->items()->create([
            'tipo' => 'articulo',
            'articulo_cobro_id' => $articulo->id,
            'nombre_snapshot' => $articulo->nombre,
            'cantidad' => $cantidad,
            'precio_catalogo' => $precioCatalogo,
            'precio_cobrado' => $precioCobrado,
            'subtotal' => round($precioCobrado * $cantidad, 2),
            'precio_modificado' => round($precioCatalogo, 2) !== round($precioCobrado, 2),
            'modificado_por' => round($precioCatalogo, 2) !== round($precioCobrado, 2) ? $user->id : null,
            'motivo_ajuste' => $validated['motivo_ajuste'] ?? null,
        ]);

        $this->consultaCobroService->recalculateTotals($consultaCobro);

        return back()->with('success', __('cobros.messages.item_added'));
    }

    public function updateItem(Request $request, ConsultaCobroItem $item)
    {
        $this->authorizeCitaAccess($item->consultaCobro->cita);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'cantidad' => ['required', 'integer', 'min:1'],
            'precio_cobrado' => ['required', 'numeric', 'min:0'],
            'motivo_ajuste' => ['nullable', 'string', 'max:255'],
        ]);

        $precioCobrado = round((float) $validated['precio_cobrado'], 2);
        $cantidad = (int) $validated['cantidad'];
        $precioCatalogo = (float) $item->precio_catalogo;

        $item->update([
            'cantidad' => $cantidad,
            'precio_cobrado' => $precioCobrado,
            'subtotal' => round($precioCobrado * $cantidad, 2),
            'precio_modificado' => round($precioCatalogo, 2) !== round($precioCobrado, 2),
            'modificado_por' => round($precioCatalogo, 2) !== round($precioCobrado, 2) ? $user->id : null,
            'motivo_ajuste' => $validated['motivo_ajuste'] ?? null,
        ]);

        $this->consultaCobroService->recalculateTotals($item->consultaCobro);

        return back()->with('success', __('cobros.messages.item_updated'));
    }

    public function destroyItem(ConsultaCobroItem $item)
    {
        $this->authorizeCitaAccess($item->consultaCobro->cita);
        $cobro = $item->consultaCobro;

        $item->delete();
        $this->consultaCobroService->recalculateTotals($cobro);

        return back()->with('success', __('cobros.messages.item_deleted'));
    }

    public function updateAfectacion(Request $request, CitaAfectacion $afectacion)
    {
        $this->authorizeCitaAccess($afectacion->consultaCobro->cita);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'estado' => ['required', 'in:pendiente_aviso,avisado,reagendada,no_localizado'],
            'notas' => ['nullable', 'string'],
        ]);

        $afectacion->update([
            'estado' => $validated['estado'],
            'notas' => $validated['notas'] ?? null,
            'avisado_at' => in_array($validated['estado'], ['avisado', 'reagendada'], true) ? now() : $afectacion->avisado_at,
            'gestionado_por' => $user->id,
        ]);

        return back()->with('success', __('cobros.messages.affectation_updated'));
    }

    private function authorizeDoctorAccess(Cita $cita): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user instanceof User || ! $user->hasRole('doctor') || $cita->doctor_id !== $user->id) {
            abort(403);
        }
    }

    private function authorizeCitaAccess(Cita $cita): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        if ($user->hasRole('root')) {
            return;
        }

        if ($user->hasRole('doctor') && $cita->doctor_id === $user->id) {
            return;
        }

        if ($user->hasRole(['asistente', 'secretaria']) && (int) $user->created_by === (int) $cita->doctor_id) {
            return;
        }

        abort(403);
    }

    private function formatArticulos($articulos): array
    {
        return $articulos->map(fn (ArticuloCobro $articulo): array => [
            'id' => $articulo->id,
            'nombre' => $articulo->nombre,
            'precio' => (float) $articulo->precio,
            'label' => $articulo->nombre.' - $'.number_format((float) $articulo->precio, 2),
        ])->values()->all();
    }
}
