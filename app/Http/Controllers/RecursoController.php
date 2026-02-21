<?php

namespace App\Http\Controllers;

use App\Models\Recurso;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class RecursoController extends Controller
{
    protected SubscriptionService $subscriptionService;

    private const RECURSOS_PERMISSION = 'manage recursos';

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $this->ensureModuleEnabled($user);

        $doctorId = $this->resolveDoctorId($user, $request->input('doctor_id'));

        $query = Recurso::where('user_id', $doctorId)->orderBy('nombre');

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo === '1');
        }

        $recursos = $query->paginate(10)->withQueryString();

        $doctors = collect();
        if ($user->hasRole('root')) {
            $doctors = User::role('doctor')->orderBy('name')->get();
        }

        return view('admin.recursos.index', [
            'recursos' => $recursos,
            'doctorId' => $doctorId,
            'doctors' => $doctors,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $this->ensureModuleEnabled($user);

        $doctorId = $this->resolveDoctorId($user, $request->input('doctor_id'));

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $color = $request->input('color') ?: '#0061F5';

        Recurso::create([
            'user_id' => $doctorId,
            'created_by' => $user->id,
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'color' => $color,
            'descripcion' => $request->descripcion,
            'activo' => true,
        ]);

        return redirect()->route('recursos.index', ['doctor_id' => $doctorId])->with('success', 'Recurso creado correctamente.');
    }

    public function update(Request $request, Recurso $recurso)
    {
        $user = Auth::user();
        $this->ensureCanManageRecurso($user, $recurso);

        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['required', 'boolean'],
        ]);

        $recurso->update([
            'nombre' => $request->nombre,
            'tipo' => $request->tipo,
            'color' => $request->color ?: '#0061F5',
            'descripcion' => $request->descripcion,
            'activo' => $request->activo,
        ]);

        return redirect()->route('recursos.index', ['doctor_id' => $recurso->user_id])->with('success', 'Recurso actualizado correctamente.');
    }

    public function destroy(Recurso $recurso)
    {
        $user = Auth::user();
        $this->ensureCanManageRecurso($user, $recurso);

        $recurso->delete();

        return redirect()->route('recursos.index', ['doctor_id' => $recurso->user_id])->with('success', 'Recurso eliminado correctamente.');
    }

    public function permisos(Request $request)
    {
        $user = Auth::user();
        if (! $user->hasRole(['root', 'doctor'])) {
            abort(403);
        }

        $doctorId = $this->resolveDoctorId($user, $request->input('doctor_id'));
        $doctor = User::findOrFail($doctorId);

        $this->ensureModuleEnabled($doctor);

        $query = User::with('roles')
            ->where(function ($q) use ($doctorId) {
                $q->where('id', $doctorId)->orWhere('created_by', $doctorId);
            })
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['doctor', 'asistente', 'secretaria']);
            });

        if ($user->hasRole('root')) {
            $doctors = User::role('doctor')->orderBy('name')->get();
        } else {
            $doctors = collect([$doctor]);
        }

        $usuarios = $query->orderBy('name')->get();

        return view('admin.recursos.permisos', [
            'doctor' => $doctor,
            'usuarios' => $usuarios,
            'doctors' => $doctors,
            'doctorId' => $doctorId,
        ]);
    }

    public function actualizarPermisos(Request $request)
    {
        $user = Auth::user();
        if (! $user->hasRole(['root', 'doctor'])) {
            abort(403);
        }

        $doctorId = $this->resolveDoctorId($user, $request->input('doctor_id'));
        $doctor = User::findOrFail($doctorId);

        $this->ensureModuleEnabled($doctor);

        $this->ensurePermissionExists();

        $ids = $request->input('usuarios', []);

        $query = User::where(function ($q) use ($doctorId) {
            $q->where('id', $doctorId)->orWhere('created_by', $doctorId);
        })->whereHas('roles', function ($q) {
            $q->whereIn('name', ['doctor', 'asistente', 'secretaria']);
        });
        $usuarios = $query->get();

        foreach ($usuarios as $usuario) {
            if ($usuario->hasPermissionTo(self::RECURSOS_PERMISSION)) {
                $usuario->revokePermissionTo(self::RECURSOS_PERMISSION);
            }
        }

        if (! empty($ids)) {
            $usuariosSeleccionados = User::whereIn('id', $ids)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['doctor', 'asistente', 'secretaria']);
                })
                ->get();
            foreach ($usuariosSeleccionados as $usuario) {
                $usuario->givePermissionTo(self::RECURSOS_PERMISSION);
            }
        }

        if (! $doctor->hasPermissionTo(self::RECURSOS_PERMISSION)) {
            $doctor->givePermissionTo(self::RECURSOS_PERMISSION);
        }

        return redirect()->route('recursos.permisos', ['doctor_id' => $doctorId])->with('success', 'Permisos actualizados correctamente.');
    }

    protected function ensureModuleEnabled(User $user): void
    {
        $this->ensurePermissionExists();

        if ($user->hasRole('root')) {
            return;
        }

        if ($user->hasRole('doctor')) {
            $enabled = $this->subscriptionService->hasActiveFeature($user, 'clinica');
            if ($enabled) {
                return;
            }
        }

        if ($user->hasRole(['asistente', 'secretaria'])) {
            $ownerId = $user->created_by;
            if (! $ownerId) {
                abort(403);
            }
            $owner = User::find($ownerId);
            if ($owner && $this->subscriptionService->hasActiveFeature($owner, 'clinica') && $user->hasPermissionTo(self::RECURSOS_PERMISSION)) {
                return;
            }
        }

        abort(403);
    }

    protected function ensureCanManageRecurso(User $user, Recurso $recurso): void
    {
        $this->ensureModuleEnabled($user);

        if ($user->hasRole('root')) {
            return;
        }

        $doctorId = $recurso->user_id;

        if ($user->hasRole('doctor') && $user->id === $doctorId) {
            return;
        }

        if ($user->hasRole(['asistente', 'secretaria']) && $user->created_by === $doctorId && $user->hasPermissionTo(self::RECURSOS_PERMISSION)) {
            return;
        }

        abort(403);
    }

    protected function resolveDoctorId(User $currentUser, ?int $requestedDoctorId): int
    {
        if ($currentUser->hasRole('root')) {
            if ($requestedDoctorId) {
                return $requestedDoctorId;
            }
            $doctor = User::role('doctor')->orderBy('name')->first();
            if ($doctor) {
                return $doctor->id;
            }
            abort(403);
        }

        if ($currentUser->hasRole('doctor')) {
            return $currentUser->id;
        }

        if ($currentUser->hasRole(['asistente', 'secretaria']) && $currentUser->created_by) {
            return $currentUser->created_by;
        }

        abort(403);
    }

    protected function ensurePermissionExists(): void
    {
        Permission::findOrCreate(self::RECURSOS_PERMISSION, 'web');
    }
}
