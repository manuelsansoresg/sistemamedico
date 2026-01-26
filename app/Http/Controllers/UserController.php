<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Clinica;
use App\Models\Consultorio;
use App\Models\Especialidad;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $clinicas = Clinica::where('activo', true)->get();
        $consultorios = Consultorio::where('activo', true)->get();
        $especialidades = Especialidad::where('activo', true)->get();

        return view('admin.users.create', compact('roles', 'clinicas', 'consultorios', 'especialidades'));
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
            'role' => ['required', 'exists:roles,name'],
            'clinicas' => ['nullable', 'array'],
            'clinicas.*' => ['exists:clinicas,id'],
            'consultorios' => ['nullable', 'array'],
            'consultorios.*' => ['exists:consultorios,id'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'cedula_profesional' => $request->cedula_profesional,
            'especialidad_id' => $request->especialidad_id,
        ]);

        $user->assignRole($request->role);

        if ($request->has('clinicas')) {
            $user->clinicas()->sync($request->clinicas);
        }

        if ($request->has('consultorios')) {
            $user->consultorios()->sync($request->consultorios);
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $clinicas = Clinica::where('activo', true)->get();
        $consultorios = Consultorio::where('activo', true)->get();
        $especialidades = Especialidad::where('activo', true)->get();
        
        return view('admin.users.edit', compact('user', 'roles', 'clinicas', 'consultorios', 'especialidades'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'telefono' => ['nullable', 'string', 'max:20'],
            'cedula_profesional' => ['nullable', 'string', 'max:50'],
            'especialidad_id' => ['nullable', 'exists:especialidades,id'],
            'role' => ['required', 'exists:roles,name'],
            'clinicas' => ['nullable', 'array'],
            'clinicas.*' => ['exists:clinicas,id'],
            'consultorios' => ['nullable', 'array'],
            'consultorios.*' => ['exists:consultorios,id'],
        ]);

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

        $user->syncRoles([$request->role]);

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

        return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminar tu propio usuario.');
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
