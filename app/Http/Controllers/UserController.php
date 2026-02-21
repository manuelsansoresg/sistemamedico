<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Consultorio;
use App\Models\Especialidad;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
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
        $query = User::with('roles');

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $currentUser->hasRole('doctor') ? $currentUser->id : $currentUser->created_by;

            $query->where(function ($q) use ($ownerId) {
                $q->where('id', $ownerId)
                    ->orWhere('created_by', $ownerId);
            });
        }

        $users = $query->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $roles = Role::whereIn('name', ['asistente', 'secretaria'])->get();
        } else {
            $roles = Role::all();
        }

        $clinicas = Clinica::where('activo', true)->get();
        $consultorios = Consultorio::where('activo', true)->get();
        $especialidades = Especialidad::where('activo', true)->get();

        $permissions = Permission::whereIn('name', [
            'descargar expedientes',
            'descargar consultas',
            'descargar estudios',
            'descargar estudios con imagenes',
        ])->get();

        return view('admin.users.create', compact('roles', 'clinicas', 'consultorios', 'especialidades', 'permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'telefono' => ['nullable', 'string', 'max:20'],
            'cedula_profesional' => ['nullable', 'string', 'max:50'],
            'especialidad_id' => ['nullable', 'exists:especialidades,id'],
            'role' => ['required', 'exists:roles,name', function ($attribute, $value, $fail) {
                /** @var \App\Models\User $currentUser */
                $currentUser = Auth::user();
                if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria']) && ! in_array($value, ['asistente', 'secretaria'])) {
                    $fail('No tienes permisos para asignar este rol.');
                }
            }],
            'clinicas' => ['nullable', 'array'],
            'clinicas.*' => ['exists:clinicas,id'],
            'consultorios' => ['nullable', 'array'],
            'consultorios.*' => ['exists:consultorios,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        $owner = $currentUser->hasRole('doctor') ? $currentUser : User::find($currentUser->created_by);

        if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria']) && in_array($request->role, ['asistente', 'secretaria'])) {
            if (! $this->subscriptionService->canCreate($owner, 'usuario')) {
                return back()->withErrors(['role' => 'Ha alcanzado el límite de usuarios permitidos por su suscripción.'])->withInput();
            }
        }

        $user = User::create([
            'name' => $request->name,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'cedula_profesional' => $request->cedula_profesional,
            'especialidad_id' => $request->especialidad_id,
            'created_by' => $owner->id,
        ]);

        $user->assignRole($request->role);

        if ($request->has('clinicas')) {
            $user->clinicas()->sync($request->clinicas);
        }

        if ($request->has('consultorios')) {
            $user->consultorios()->sync($request->consultorios);
        }

        if (in_array($request->role, ['asistente', 'secretaria']) && $request->has('permissions')) {
            $user->syncPermissions($request->permissions);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if ($user->hasRole('doctor')) {
            $createdClinicasIds = Clinica::where('created_by', $user->id)->pluck('id')->toArray();
            if (! empty($createdClinicasIds)) {
                $user->clinicas()->syncWithoutDetaching($createdClinicasIds);
            }

            $createdConsultoriosIds = Consultorio::where('created_by', $user->id)->pluck('id')->toArray();
            if (! empty($createdConsultoriosIds)) {
                $user->consultorios()->syncWithoutDetaching($createdConsultoriosIds);
            }
        }

        if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $roles = Role::whereIn('name', ['asistente', 'secretaria'])->get();
        } else {
            $roles = Role::all();
        }

        $clinicas = Clinica::where('activo', true)->get();
        $consultorios = Consultorio::where('activo', true)->get();
        $especialidades = Especialidad::where('activo', true)->get();

        $permissions = Permission::whereIn('name', [
            'descargar expedientes',
            'descargar consultas',
            'descargar estudios',
            'descargar estudios con imagenes',
        ])->get();

        if ($currentUser->hasRole(['asistente', 'secretaria']) && $user->hasRole('doctor')) {
            abort(403, 'No tienes permiso para editar al doctor.');
        }

        return view('admin.users.edit', compact('user', 'roles', 'clinicas', 'consultorios', 'especialidades', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if ($currentUser->hasRole(['asistente', 'secretaria']) && $user->hasRole('doctor')) {
            abort(403, 'No tienes permiso para editar al doctor.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'telefono' => ['nullable', 'string', 'max:20'],
            'cedula_profesional' => ['nullable', 'string', 'max:50'],
            'especialidad_id' => ['nullable', 'exists:especialidades,id'],
            'clinicas' => ['nullable', 'array'],
            'clinicas.*' => ['exists:clinicas,id'],
            'consultorios' => ['nullable', 'array'],
            'consultorios.*' => ['exists:consultorios,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ];

        // Only validate role if not doctor editing self
        $isDoctorSelfEdit = $currentUser->hasRole('doctor') && $user->id === $currentUser->id;

        if (! $isDoctorSelfEdit) {
            $rules['role'] = ['required', 'exists:roles,name', function ($attribute, $value, $fail) use ($currentUser) {
                if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria']) && ! in_array($value, ['asistente', 'secretaria'])) {
                    $fail('No tienes permisos para asignar este rol.');
                }
            }];
        }

        $request->validate($rules);

        $userData = [
            'name' => $request->name,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'cedula_profesional' => $request->cedula_profesional,
            'especialidad_id' => $request->especialidad_id,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        if (! $isDoctorSelfEdit) {
            $user->syncRoles([$request->role]);
        }

        if (! $isDoctorSelfEdit) {
            if ($request->has('clinicas')) {
                $user->clinicas()->sync($request->clinicas);
            } else {
                $user->clinicas()->detach();
            }

            if ($request->has('consultorios')) {
                $user->consultorios()->sync($request->consultorios);
            } else {
                $user->consultorios()->detach();
            }
        }

        $targetRole = $isDoctorSelfEdit ? $user->roles->first()?->name : $request->role;

        if (in_array($targetRole, ['asistente', 'secretaria'])) {
            if ($request->has('permissions')) {
                $user->syncPermissions($request->permissions);
            } else {
                $user->syncPermissions([]);
            }
        } else {
            $user->syncPermissions([]);
        }

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $currentUser->hasRole('doctor') ? $currentUser->id : $currentUser->created_by;

            // Check if target user belongs to the owner's scope
            $isOwned = ($user->id === $ownerId) || ($user->created_by === $ownerId);

            if (! $isOwned) {
                abort(403, 'No tiene permiso para eliminar este usuario.');
            }

            // Assistants cannot delete the doctor (owner)
            if ($user->id === $ownerId) {
                abort(403, 'No tiene permiso para eliminar al doctor titular.');
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
