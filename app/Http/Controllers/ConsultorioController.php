<?php

namespace App\Http\Controllers;

use App\Models\Consultorio;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultorioController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Consultorio::with('creator')->latest();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $query->where('created_by', $ownerId);
        }

        $consultorios = $query->paginate(10);

        return view('admin.consultorios.index', compact('consultorios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.consultorios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $owner = $user->hasRole('doctor') ? $user : \App\Models\User::find($user->created_by);

        if (! $this->subscriptionService->canCreate($owner, 'consultorio')) {
            return redirect()->back()->with('error', __('consultorios.errors.subscription_limit_reached'));
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'activo' => 'boolean',
        ]);

        $origen = $this->subscriptionService->pickOriginSubscription($owner, 'consultorio');

        $consultorio = Consultorio::create([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'activo' => $request->has('activo'),
            'created_by' => $owner->id,
            'origen_suscripcion_id' => $origen ? $origen->id : null,
            'origen_tipo' => $origen ? $origen->tipo : null,
        ]);

        if ($owner && $owner->hasRole('doctor')) {
            $consultorio->users()->syncWithoutDetaching([$owner->id]);
        }

        return redirect()->route('consultorios.index')->with('success', __('consultorios.messages.created_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Consultorio $consultorio)
    {
        return view('admin.consultorios.edit', compact('consultorio'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Consultorio $consultorio)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'activo' => 'boolean',
        ]);

        $consultorio->update([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('consultorios.index')->with('success', __('consultorios.messages.updated_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consultorio $consultorio)
    {
        $consultorio->delete();

        return redirect()->route('consultorios.index')->with('success', __('consultorios.messages.deleted_success'));
    }
}
