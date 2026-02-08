<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

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

        if ($user->hasRole('doctor')) {
            $query->where('created_by', $user->id);
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
        if (!$this->subscriptionService->canCreate($user, 'clinica')) {
            return redirect()->back()->with('error', 'Ha alcanzado el límite de clínicas permitidas por su suscripción.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'logotipo' => 'nullable|image|max:2048', // 2MB Max
            'ubicacion' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'activo' => 'boolean',
        ]);

        $logotipoPath = null;
        if ($request->hasFile('logotipo')) {
            $file = $request->file('logotipo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('clinicas'), $filename);
            $logotipoPath = 'clinicas/' . $filename;
        }

        Clinica::create([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'logotipo' => $logotipoPath,
            'ubicacion' => $request->ubicacion,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'activo' => $request->has('activo'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('clinicas.index')->with('success', 'Clínica creada exitosamente.');
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
            'logotipo' => 'nullable|image|max:2048', // 2MB Max
            'ubicacion' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'activo' => 'boolean',
        ]);

        $logotipoPath = $clinica->logotipo;
        if ($request->hasFile('logotipo')) {
            // Delete old image if exists
            if ($logotipoPath && File::exists(public_path($logotipoPath))) {
                File::delete(public_path($logotipoPath));
            }
            $file = $request->file('logotipo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('clinicas'), $filename);
            $logotipoPath = 'clinicas/' . $filename;
        }

        $clinica->update([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'logotipo' => $logotipoPath,
            'ubicacion' => $request->ubicacion,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'activo' => $request->has('activo'),
        ]);

        return redirect()->route('clinicas.index')->with('success', 'Clínica actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clinica $clinica)
    {
        if ($clinica->logotipo && File::exists(public_path($clinica->logotipo))) {
            File::delete(public_path($clinica->logotipo));
        }
        $clinica->delete();
        return redirect()->route('clinicas.index')->with('success', 'Clínica eliminada exitosamente.');
    }
}
