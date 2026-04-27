<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Clinica;
use App\Models\Consultorio;
use App\Models\Especialidad;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;

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

        $query->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'paciente');
        });

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
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'role' => ['required', 'exists:roles,name', function ($attribute, $value, $fail) {
                /** @var \App\Models\User $currentUser */
                $currentUser = Auth::user();
                if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria']) && ! in_array($value, ['asistente', 'secretaria'])) {
                    $fail(__('users.errors.cannot_assign_role'));
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
                return back()->withErrors(['role' => __('users.errors.subscription_limit_reached')])->withInput();
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

        if ($request->hasFile('profile_photo')) {
            $this->storeProfilePhotoFromRequest($request, $user, $currentUser);
        }

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

        try {
            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'asignar_roles_permisos',
                'section' => 'usuarios',
                'model_type' => get_class($user),
                'model_id' => $user->id,
                'payload' => [
                    'new' => [
                        'roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames()->values()->all() : $user->roles()->pluck('name')->toArray(),
                        'permissions' => method_exists($user, 'getPermissionNames') ? $user->getPermissionNames()->values()->all() : $user->permissions()->pluck('name')->toArray(),
                    ],
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }

        return redirect()->route('users.index')->with('success', __('users.messages.created_success'));
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
            abort(403, __('users.errors.cannot_edit_doctor'));
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

        $rolesAntes = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->values()->all() : $user->roles()->pluck('name')->toArray();
        $permisosAntes = method_exists($user, 'getPermissionNames') ? $user->getPermissionNames()->values()->all() : $user->permissions()->pluck('name')->toArray();

        if ($currentUser->hasRole(['asistente', 'secretaria']) && $user->hasRole('doctor')) {
            abort(403, __('users.errors.cannot_edit_doctor'));
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
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
                    $fail(__('users.errors.cannot_assign_role'));
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

        if ($request->hasFile('profile_photo')) {
            $this->storeProfilePhotoFromRequest($request, $user, $currentUser);
        }

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

        $rolesDespues = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->values()->all() : $user->roles()->pluck('name')->toArray();
        $permisosDespues = method_exists($user, 'getPermissionNames') ? $user->getPermissionNames()->values()->all() : $user->permissions()->pluck('name')->toArray();

        if ($rolesAntes !== $rolesDespues || $permisosAntes !== $permisosDespues) {
            try {
                AuditLog::create([
                    'user_id' => $currentUser->id,
                    'action' => 'cambio_roles_permisos',
                    'section' => 'usuarios',
                    'model_type' => get_class($user),
                    'model_id' => $user->id,
                    'payload' => [
                        'old' => [
                            'roles' => $rolesAntes,
                            'permissions' => $permisosAntes,
                        ],
                        'new' => [
                            'roles' => $rolesDespues,
                            'permissions' => $permisosDespues,
                        ],
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('users.index')->with('success', __('users.messages.updated_success'));
    }

    private function storeProfilePhotoFromRequest(Request $request, User $user, User $currentUser): void
    {
        $file = $request->file('profile_photo');
        if (! $file) {
            return;
        }

        $oldPath = $user->profile_photo_path;
        $path = $file->store('profile-photos', 'public');

        try {
            $absolutePath = Storage::disk('public')->path($path);
            $this->resizeProfilePhotoIfPossible($absolutePath);
        } catch (Throwable $e) {
            Log::warning(__('users.logs.profile_photo_process_failed'), [
                'error' => $e->getMessage(),
            ]);
        }

        $user->profile_photo_path = $path;
        $user->save();

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        try {
            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'actualizar_foto_perfil',
                'section' => 'usuarios',
                'model_type' => User::class,
                'model_id' => $user->id,
                'payload' => [
                    'photo_path' => $path,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function resizeProfilePhotoIfPossible(string $absolutePath): void
    {
        if (! extension_loaded('gd')) {
            return;
        }

        if (! is_file($absolutePath)) {
            return;
        }

        $info = @getimagesize($absolutePath);
        if (! $info || empty($info['mime'])) {
            return;
        }

        $mime = $info['mime'];

        $src = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($absolutePath),
            'image/png' => @imagecreatefrompng($absolutePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : null,
            default => null,
        };

        if (! $src) {
            return;
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);

        if ($srcW <= 0 || $srcH <= 0) {
            imagedestroy($src);

            return;
        }

        $side = min($srcW, $srcH);
        $srcX = (int) floor(($srcW - $side) / 2);
        $srcY = (int) floor(($srcH - $side) / 2);

        $dstSize = 400;
        $dst = imagecreatetruecolor($dstSize, $dstSize);
        if (! $dst) {
            imagedestroy($src);

            return;
        }

        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $dstSize, $dstSize, $transparent);

        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $dstSize, $dstSize, $side, $side);

        match ($mime) {
            'image/jpeg' => imagejpeg($dst, $absolutePath, 85),
            'image/png' => imagepng($dst, $absolutePath, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($dst, $absolutePath, 85) : null,
            default => null,
        };

        imagedestroy($dst);
        imagedestroy($src);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')->with('error', __('users.errors.cannot_delete_self'));
        }

        if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $currentUser->hasRole('doctor') ? $currentUser->id : $currentUser->created_by;

            // Check if target user belongs to the owner's scope
            $isOwned = ($user->id === $ownerId) || ($user->created_by === $ownerId);

            if (! $isOwned) {
                abort(403, __('users.errors.cannot_delete_user'));
            }

            // Assistants cannot delete the doctor (owner)
            if ($user->id === $ownerId) {
                abort(403, __('users.errors.cannot_delete_owner_doctor'));
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', __('users.messages.deleted_success'));
    }
}
