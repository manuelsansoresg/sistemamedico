<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Especialidad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class BrandingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $especialidades = Especialidad::where('activo', true)->get();
        $isDoctor = $user->hasRole('doctor');

        return view('admin.branding.edit', compact('user', 'especialidades', 'isDoctor'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $isDoctor = $user->hasRole('doctor');

        if ($isDoctor) {
            $request->validate([
                'name' => 'required|string|max:255',
                'apellido_paterno' => 'nullable|string|max:255',
                'apellido_materno' => 'nullable|string|max:255',
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'telefono' => 'nullable|string|max:20',
                'cedula_profesional' => 'nullable|string|max:50',
                'especialidad_id' => 'nullable|exists:especialidades,id',
                'curp' => 'nullable|string|max:18',
                'fecha_nacimiento' => 'nullable|date',
                'sexo' => ['nullable', Rule::in(['M', 'F'])],
                'direccion' => 'nullable|string',
                'numero_imss' => 'nullable|string|max:50',
                'peso' => 'nullable|numeric|min:0',
                'estatura' => 'nullable|numeric|min:0',
                'alergias' => 'nullable|string',
                'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'password' => 'nullable|min:8|confirmed',
            ]);
        } else {
            $request->validate([
                'name' => 'required|string|max:255',
                'apellido_paterno' => 'nullable|string|max:255',
                'apellido_materno' => 'nullable|string|max:255',
                'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'telefono' => 'nullable|string|max:20',
                'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'password' => 'nullable|min:8|confirmed',
            ]);
        }

        $oldData = $user->only([
            'name', 'apellido_paterno', 'apellido_materno', 'email', 'telefono',
            'cedula_profesional', 'especialidad_id', 'curp', 'fecha_nacimiento',
            'sexo', 'direccion', 'numero_imss', 'peso', 'estatura', 'alergias',
        ]);

        $updates = [
            'name' => $request->name,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email' => $request->email,
            'telefono' => $request->telefono,
        ];

        if ($isDoctor) {
            $updates['cedula_profesional'] = $request->cedula_profesional;
            $updates['especialidad_id'] = $request->especialidad_id;
            $updates['curp'] = $request->curp;
            $updates['fecha_nacimiento'] = $request->fecha_nacimiento;
            $updates['sexo'] = $request->sexo;
            $updates['direccion'] = $request->direccion;
            $updates['numero_imss'] = $request->numero_imss;
            $updates['peso'] = $request->peso;
            $updates['estatura'] = $request->estatura;
            $updates['alergias'] = $request->alergias;
        }

        if ($request->filled('password')) {
            $updates['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $updates['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user->update($updates);

        $newData = $user->only([
            'name', 'apellido_paterno', 'apellido_materno', 'email', 'telefono',
            'cedula_profesional', 'especialidad_id', 'curp', 'fecha_nacimiento',
            'sexo', 'direccion', 'numero_imss', 'peso', 'estatura', 'alergias',
        ]);

        try {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'actualizar_perfil',
                'section' => 'usuarios',
                'model_type' => User::class,
                'model_id' => $user->id,
                'payload' => [
                    'old' => $oldData,
                    'new' => $newData,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('No se pudo registrar auditoría actualizar_perfil', ['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('branding.edit')
            ->with('success', __('branding.profile_updated'));
    }
}
