<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class PacienteController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = User::role('paciente');

        // If user is doctor, show only their patients (assigned or created by them)
        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            
            $query->where(function($q) use ($ownerId) {
                $q->whereHas('doctors', function ($subQ) use ($ownerId) {
                    $subQ->where('users.id', $ownerId);
                })
                ->orWhere('created_by', $ownerId);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('apellido_paterno', 'like', "%{$search}%")
                  ->orWhere('apellido_materno', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('curp', 'like', "%{$search}%");
            });
        }

        $pacientes = $query->paginate(10)->withQueryString();

        return view('admin.pacientes.index', compact('pacientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $doctors = [];
        if (Auth::user()->hasRole('root')) {
            $doctors = User::role('doctor')->get();
        }

        return view('admin.pacientes.create', compact('doctors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $owner = $user->hasRole('doctor') ? $user : User::find($user->created_by);

        if (!$this->subscriptionService->canCreate($owner, 'paciente')) {
            return redirect()->back()->with('error', 'Ha alcanzado el límite de pacientes permitidos por su suscripción.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'telefono' => ['nullable', 'string', 'max:20'],
            'curp' => ['nullable', 'string', 'max:18'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'in:M,F'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'numero_imss' => ['nullable', 'string', 'max:20'],
            'activo' => ['boolean'],
            'perfil_compartido' => ['boolean'],
        ];

        if (Auth::user()->hasRole('root')) {
            $rules['doctor_id'] = ['required', 'exists:users,id'];
        }

        $request->validate($rules);

        $user = User::create([
            'name' => $request->name,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'curp' => $request->curp,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'sexo' => $request->sexo,
            'direccion' => $request->direccion,
            'numero_imss' => $request->numero_imss,
            'activo' => $request->has('activo'),
            'perfil_compartido' => $request->has('perfil_compartido'),
            'created_by' => $owner->id,
        ]);

        $user->assignRole('paciente');

        // Link to doctor
        if (Auth::user()->hasRole('root')) {
            $user->doctors()->attach($request->doctor_id);
        } elseif (Auth::user()->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $user->doctors()->attach($owner->id);
        }

        return redirect()->route('pacientes.index')->with('success', 'Paciente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $paciente)
    {
        $this->authorizeAccess($paciente);
        
        return view('admin.pacientes.edit', compact('paciente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $paciente)
    {
        $this->authorizeAccess($paciente);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $paciente->id],
            'telefono' => ['nullable', 'string', 'max:20'],
            'curp' => ['nullable', 'string', 'max:18'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'in:M,F'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'numero_imss' => ['nullable', 'string', 'max:20'],
            'activo' => ['boolean'],
            'perfil_compartido' => ['boolean'],
        ]);

        $data = [
            'name' => $request->name,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'curp' => $request->curp,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'sexo' => $request->sexo,
            'direccion' => $request->direccion,
            'numero_imss' => $request->numero_imss,
            'activo' => $request->has('activo'),
            'perfil_compartido' => $request->has('perfil_compartido'),
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $paciente->update($data);

        return redirect()->route('pacientes.index')->with('success', 'Paciente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $paciente)
    {
        $this->authorizeAccess($paciente);

        $paciente->delete();
        return redirect()->route('pacientes.index')->with('success', 'Paciente eliminado exitosamente.');
    }

    private function authorizeAccess(User $paciente)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('root')) {
            return;
        }

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            
            // Check if patient is linked to owner doctor
            $linked = $paciente->doctors()->where('users.id', $ownerId)->exists();
            // Or created by owner
            $created = $paciente->created_by == $ownerId;

            if (!$linked && !$created) {
                abort(403, 'No tiene permiso para acceder a este paciente.');
            }
            return;
        }

        abort(403, 'Acceso no autorizado.');
    }
}
