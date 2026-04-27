<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClinicaController extends Controller
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
        $query = Clinica::with('creator')->latest();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $query->where('created_by', $ownerId);
        }

        $clinicas = $query->paginate(10);

        return view('admin.clinicas.index', compact('clinicas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.clinicas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof \App\Models\User) {
            abort(403);
        }
        $owner = $user->hasRole('doctor') ? $user : \App\Models\User::find($user->created_by);

        if (! $this->subscriptionService->canCreate($owner, 'clinica')) {
            return redirect()->back()->with('error', __('common.errors.subscription_limit_reached'));
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'ubicacion' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'activo' => 'boolean',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('clinic-logos', 'public');
        }

        $origen = $this->subscriptionService->pickOriginSubscription($owner, 'clinica');

        $clinica = Clinica::create([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'logo' => $logoPath,
            'ubicacion' => $request->ubicacion,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'activo' => $request->has('activo'),
            'created_by' => $owner->id,
            'origen_suscripcion_id' => $origen ? $origen->id : null,
            'origen_tipo' => $origen ? $origen->tipo : null,
        ]);

        if ($owner && $owner->hasRole('doctor')) {
            $clinica->users()->syncWithoutDetaching([$owner->id]);
        }

        return redirect()->route('clinicas.index')->with('success', __('common.messages.created_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Clinica $clinica)
    {
        return view('admin.clinicas.edit', compact('clinica'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clinica $clinica)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'ubicacion' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'activo' => 'boolean',
        ]);

        $logoPath = $clinica->logo;
        if ($request->hasFile('logo')) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }

            $logoPath = $request->file('logo')->store('clinic-logos', 'public');
        }

        $clinica->update([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'logo' => $logoPath,
            'ubicacion' => $request->ubicacion,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('clinicas.index')->with('success', __('common.messages.updated_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clinica $clinica)
    {
        if ($clinica->logo) {
            Storage::disk('public')->delete($clinica->logo);
        }
        $clinica->delete();

        return redirect()->route('clinicas.index')->with('success', __('common.messages.deleted_success'));
    }
}
