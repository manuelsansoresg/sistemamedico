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

        if ($user->hasRole('doctor')) {
            $query->where('created_by', $user->id);
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
        $user = Auth::user();
        if (!$this->subscriptionService->canCreate($user, 'consultorio')) {
            return redirect()->back()->with('error', 'Ha alcanzado el límite de consultorios permitidos por su suscripción.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'activo' => 'boolean',
        ]);

        Consultorio::create([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'activo' => $request->has('activo'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('consultorios.index')->with('success', 'Consultorio creado exitosamente.');
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

        return redirect()->route('consultorios.index')->with('success', 'Consultorio actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consultorio $consultorio)
    {
        $consultorio->delete();
        return redirect()->route('consultorios.index')->with('success', 'Consultorio eliminado exitosamente.');
    }
}
