<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Throwable;

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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $query = User::role('paciente');

        // If user is doctor, show only their patients (assigned or created by them)
        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;

            $query->where(function ($q) use ($ownerId) {
                $q->whereHas('doctors', function ($subQ) use ($ownerId) {
                    $subQ->where('users.id', $ownerId);
                })
                    ->orWhere('created_by', $ownerId);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
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
     * Listado enfocado a gestión de perfiles compartidos.
     */
    public function sharedIndex(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = User::role('paciente')->with('doctors');

        if ($request->filled('paciente_id')) {
            $query->where('id', $request->paciente_id);
        }

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;

            $query->where(function ($q) use ($ownerId) {
                $q->whereHas('doctors', function ($subQ) use ($ownerId) {
                    $subQ->where('users.id', $ownerId);
                })
                    ->orWhere('created_by', $ownerId);
            });
        }

        $estado = $request->input('estado', 'todos');

        if ($estado === 'compartidos') {
            $query->where('perfil_compartido', true);
        } elseif ($estado === 'no_compartidos') {
            $query->where('perfil_compartido', false);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('apellido_materno', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('curp', 'like', "%{$search}%");
            });
        }

        if ($user->hasRole('root') && $request->filled('doctor_id')) {
            $doctorId = $request->doctor_id;

            $query->where(function ($q) use ($doctorId) {
                $q->where('created_by', $doctorId)
                    ->orWhereHas('doctors', function ($subQ) use ($doctorId) {
                        $subQ->where('users.id', $doctorId);
                    });
            });
        }

        $pacientes = $query->paginate(10)->withQueryString();

        $doctors = $user->hasRole('root') ? User::role('doctor')->get() : collect();

        $canSharePacientes = null;

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $owner = $user->hasRole('doctor') ? $user : User::find($ownerId);

            if ($owner) {
                $canSharePacientes = $this->subscriptionService->hasActiveFeature($owner, 'paciente');
            }
        }

        return view('admin.pacientes.shared', [
            'pacientes' => $pacientes,
            'doctors' => $doctors,
            'estado' => $estado,
            'canSharePacientes' => $canSharePacientes,
            'isRoot' => $user->hasRole('root'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $doctors = [];
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if ($authUser->hasRole('root')) {
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
        $ownerForLimits = $owner ?: $user;

        if (! $this->subscriptionService->canCreate($ownerForLimits, 'paciente')) {
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
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if ($authUser->hasRole('root')) {
            $rules['doctor_id'] = ['required', 'exists:users,id'];
        }

        $request->validate($rules);

        $newUser = User::create([
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
            'perfil_compartido' => false,
            'created_by' => $ownerForLimits->id,
        ]);

        if ($request->hasFile('profile_photo')) {
            $this->storeProfilePhotoFromRequest($request, $newUser, $user);
        }

        $newUser->assignRole('paciente');

        // Link to doctor
        if ($authUser->hasRole('root')) {
            $newUser->doctors()->attach($request->doctor_id);
        } elseif ($authUser->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $newUser->doctors()->attach($owner->id);
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

        $currentUser = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['nullable', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$paciente->id],
            'telefono' => ['nullable', 'string', 'max:20'],
            'curp' => ['nullable', 'string', 'max:18'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', 'in:M,F'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'numero_imss' => ['nullable', 'string', 'max:20'],
            'activo' => ['boolean'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
        ];

        $data['perfil_compartido'] = $paciente->perfil_compartido;

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $paciente->update($data);

        if ($request->hasFile('profile_photo') && $currentUser instanceof User) {
            $this->storeProfilePhotoFromRequest($request, $paciente, $currentUser);
        }

        return redirect()->route('pacientes.index')->with('success', 'Paciente actualizado exitosamente.');
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
            Log::warning('No se pudo procesar la foto de perfil', [
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
                'section' => 'pacientes',
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
     * Marcar un paciente como con perfil compartido.
     */
    public function share(Request $request, User $paciente)
    {
        $this->authorizeAccess($paciente);

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if ($paciente->perfil_compartido) {
            return back()->with('success', 'El perfil del paciente ya está compartido.');
        }

        if ($currentUser->hasRole('root')) {
            $request->validate([
                'doctor_id' => ['required', 'exists:users,id'],
            ]);

            $doctor = User::role('doctor')->findOrFail($request->doctor_id);

            $suscripcion = \App\Models\Suscripcion::where('user_id', $doctor->id)
                ->where('tipo', 'individual')
                ->where('estatus_pago', 'pagado')
                ->where(function ($q) {
                    $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now());
                })
                ->whereHas('catalogo', function ($q) {
                    $q->whereRaw("LOWER(nombre) like '%paciente%'");
                })
                ->get()
                ->first(function ($sub) {
                    $usados = DB::table('doctor_patient')->where('suscripcion_id', $sub->id)->count();

                    return $usados < ($sub->cantidad ?? 0);
                });

            if (! $suscripcion) {
                return back()->with('error', 'No hay suscripciones de Paciente disponibles para asignar.');
            }

            $paciente->perfil_compartido = true;

            if (! $paciente->created_by) {
                $paciente->created_by = $doctor->id;
            }

            $paciente->save();

            if (! $paciente->doctors()->where('users.id', $doctor->id)->exists()) {
                $paciente->doctors()->attach($doctor->id, ['suscripcion_id' => $suscripcion->id]);
            } else {
                $paciente->doctors()->updateExistingPivot($doctor->id, ['suscripcion_id' => $suscripcion->id]);
            }

            try {
                AuditLog::create([
                    'user_id' => $currentUser->id,
                    'action' => 'compartir_paciente',
                    'section' => 'pacientes',
                    'model_type' => get_class($paciente),
                    'model_id' => $paciente->id,
                    'payload' => [
                        'doctor_id' => $doctor->id,
                        'suscripcion_id' => $suscripcion->id,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                report($e);
            }

            return back()->with('success', 'Perfil compartido correctamente.');
        }

        if ($currentUser->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $currentUser->hasRole('doctor') ? $currentUser->id : $currentUser->created_by;
            $owner = $currentUser->hasRole('doctor') ? $currentUser : User::find($ownerId);

            if (! $owner || ! $this->subscriptionService->hasActiveFeature($owner, 'paciente')) {
                return back()->with('error', 'Necesita una suscripción de Paciente activa para compartir perfiles.');
            }

            $suscripcion = \App\Models\Suscripcion::where('user_id', $owner->id)
                ->where('tipo', 'individual')
                ->where('estatus_pago', 'pagado')
                ->where(function ($q) {
                    $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now());
                })
                ->whereHas('catalogo', function ($q) {
                    $q->whereRaw("LOWER(nombre) like '%paciente%'");
                })
                ->get()
                ->first(function ($sub) {
                    $usados = DB::table('doctor_patient')->where('suscripcion_id', $sub->id)->count();

                    return $usados < ($sub->cantidad ?? 0);
                });

            if (! $suscripcion) {
                return back()->with('error', 'No hay suscripciones de Paciente disponibles para asignar.');
            }

            $paciente->perfil_compartido = true;

            if (! $paciente->created_by) {
                $paciente->created_by = $owner->id;
            }

            $paciente->save();

            if (! $paciente->doctors()->where('users.id', $owner->id)->exists()) {
                $paciente->doctors()->attach($owner->id, ['suscripcion_id' => $suscripcion->id]);
            } else {
                $paciente->doctors()->updateExistingPivot($owner->id, ['suscripcion_id' => $suscripcion->id]);
            }

            try {
                AuditLog::create([
                    'user_id' => $currentUser->id,
                    'action' => 'compartir_paciente',
                    'section' => 'pacientes',
                    'model_type' => get_class($paciente),
                    'model_id' => $paciente->id,
                    'payload' => [
                        'doctor_id' => $owner->id,
                        'suscripcion_id' => $suscripcion->id,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                report($e);
            }

            return back()->with('success', 'Perfil compartido correctamente.');
        }

        return back()->with('error', 'No tiene permiso para compartir este paciente.');
    }

    /**
     * Quitar el perfil compartido de un paciente (solo Root).
     */
    public function unshare(User $paciente)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->hasRole('root')) {
            return back()->with('error', 'Solo el rol Root puede quitar el perfil compartido.');
        }

        $doctorId = $paciente->doctors()->pluck('users.id')->first();
        $paciente->perfil_compartido = false;
        $paciente->save();

        try {
            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'dejar_compartir_paciente',
                'section' => 'pacientes',
                'model_type' => get_class($paciente),
                'model_id' => $paciente->id,
                'payload' => [
                    'doctor_id' => $doctorId,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Perfil compartido eliminado correctamente.');
    }

    /**
     * Toggle compartir (Root): si no está compartido lo comparte (requiere doctor_id),
     * si ya está compartido lo descomparte.
     */
    public function toggleShare(Request $request, User $paciente)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if (! $currentUser->hasRole('root')) {
            return back()->with('error', 'Solo el rol Root puede alternar el estado de compartir.');
        }

        if ($paciente->perfil_compartido) {
            $doctorId = $paciente->doctors()->pluck('users.id')->first();
            $paciente->perfil_compartido = false;
            $paciente->save();

            $doctor = $paciente->doctors()->first();
            if ($doctor) {
                $paciente->doctors()->updateExistingPivot($doctor->id, ['suscripcion_id' => null]);
            }

            try {
                AuditLog::create([
                    'user_id' => $currentUser->id,
                    'action' => 'dejar_compartir_paciente',
                    'section' => 'pacientes',
                    'model_type' => get_class($paciente),
                    'model_id' => $paciente->id,
                    'payload' => [
                        'doctor_id' => $doctorId,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (Throwable $e) {
                report($e);
            }

            return back()->with('success', 'Perfil compartido eliminado correctamente.');
        }

        $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
        ]);

        $doctor = User::role('doctor')->findOrFail($request->doctor_id);

        $suscripcion = \App\Models\Suscripcion::where('user_id', $doctor->id)
            ->where('tipo', 'individual')
            ->where('estatus_pago', 'pagado')
            ->where(function ($q) {
                $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now());
            })
            ->whereHas('catalogo', function ($q) {
                $q->whereRaw("LOWER(nombre) like '%paciente%'");
            })
            ->get()
            ->first(function ($sub) {
                $usados = DB::table('doctor_patient')->where('suscripcion_id', $sub->id)->count();

                return $usados < ($sub->cantidad ?? 0);
            });

        if (! $suscripcion) {
            return back()->with('error', 'No hay suscripciones de Paciente disponibles para asignar.');
        }

        $paciente->perfil_compartido = true;
        if (! $paciente->created_by) {
            $paciente->created_by = $doctor->id;
        }
        $paciente->save();

        if (! $paciente->doctors()->where('users.id', $doctor->id)->exists()) {
            $paciente->doctors()->attach($doctor->id, ['suscripcion_id' => $suscripcion->id]);
        } else {
            $paciente->doctors()->updateExistingPivot($doctor->id, ['suscripcion_id' => $suscripcion->id]);
        }

        try {
            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => 'compartir_paciente',
                'section' => 'pacientes',
                'model_type' => get_class($paciente),
                'model_id' => $paciente->id,
                'payload' => [
                    'doctor_id' => $doctor->id,
                    'suscripcion_id' => $suscripcion->id,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Perfil compartido correctamente.');
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

            if (! $linked && ! $created) {
                abort(403, 'No tiene permiso para acceder a este paciente.');
            }

            return;
        }

        abort(403, 'Acceso no autorizado.');
    }
}
