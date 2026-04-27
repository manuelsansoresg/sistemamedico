<?php

namespace App\Http\Controllers;

use App\Models\Pendiente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PendienteController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:doctor|asistente|secretaria']);
    }

    private function resolveOwnerId($user): int
    {
        if ($user->hasRole('doctor')) {
            return $user->id;
        }

        if ($user->hasRole(['asistente', 'secretaria']) && $user->created_by) {
            return $user->created_by;
        }

        abort(403);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ownerId = $this->resolveOwnerId($user);

        $pendientes = Pendiente::where('user_id', $ownerId)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc')
            ->paginate(10);

        return view('doctor.pendientes.index', compact('pendientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('doctor.pendientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recordatorio' => 'required',
            'fecha' => 'required',
            'hora' => 'required',
            'activo' => 'sometimes',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ownerId = $this->resolveOwnerId($user);

        $validated['user_id'] = $ownerId;
        $validated['activo'] = $request->has('activo') ? true : false;

        Pendiente::create($validated);

        return redirect()->route('pendientes.index')->with('success', __('pendientes.messages.created_success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pendiente $pendiente)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ownerId = $this->resolveOwnerId($user);

        // Authorization check
        if ($pendiente->user_id !== $ownerId) {
            abort(403);
        }

        return view('doctor.pendientes.edit', compact('pendiente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pendiente $pendiente)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ownerId = $this->resolveOwnerId($user);

        // Authorization check
        if ($pendiente->user_id !== $ownerId) {
            abort(403);
        }

        $validated = $request->validate([
            'recordatorio' => 'required',
            'fecha' => 'required',
            'hora' => 'required',
            'activo' => 'sometimes',
        ]);

        $validated['activo'] = $request->has('activo') ? true : false;

        $pendiente->update($validated);

        return redirect()->route('pendientes.index')->with('success', __('pendientes.messages.updated_success'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pendiente $pendiente)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $ownerId = $this->resolveOwnerId($user);

        // Authorization check
        if ($pendiente->user_id !== $ownerId) {
            abort(403);
        }

        $pendiente->delete();

        return redirect()->route('pendientes.index')->with('success', __('pendientes.messages.deleted_success'));
    }
}
