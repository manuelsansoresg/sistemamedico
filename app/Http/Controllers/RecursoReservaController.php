<?php

namespace App\Http\Controllers;

use App\Models\Recurso;
use App\Models\RecursoReserva;
use App\Models\User;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class RecursoReservaController extends Controller
{
    protected SubscriptionService $subscriptionService;
    private const RECURSOS_PERMISSION = 'manage recursos';

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function calendario(Request $request)
    {
        $user = Auth::user();

        $this->ensureModuleEnabled($user);

        $doctorId = $this->resolveDoctorId($user, $request->input('doctor_id'));

        $recursos = Recurso::where('user_id', $doctorId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $doctors = collect();
        if ($user->hasRole('root')) {
            $doctors = User::role('doctor')->orderBy('name')->get();
        }

        $usuariosQuery = User::with('roles')
            ->where(function ($q) use ($doctorId) {
                $q->where('id', $doctorId)->orWhere('created_by', $doctorId);
            })
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['doctor', 'asistente', 'secretaria']);
            });

        $usuarios = $usuariosQuery->get()->filter(function (User $u) {
            return $u->hasRole('doctor') || $u->hasPermissionTo(self::RECURSOS_PERMISSION);
        })->values();

        return view('admin.recursos.agenda', [
            'recursos' => $recursos,
            'doctorId' => $doctorId,
            'doctors' => $doctors,
            'usuarios' => $usuarios,
        ]);
    }

    public function eventos(Request $request)
    {
        $user = Auth::user();

        $this->ensureModuleEnabled($user);

        $doctorId = $this->resolveDoctorId($user, $request->input('doctor_id'));

        $query = RecursoReserva::with(['recurso', 'user'])->whereHas('recurso', function ($q) use ($doctorId) {
            $q->where('user_id', $doctorId);
        });

        if ($request->filled('recurso_id')) {
            $query->where('recurso_id', $request->recurso_id);
        }

        if ($request->filled('start')) {
            $query->where('fin', '>=', $request->start);
        }

        if ($request->filled('end')) {
            $query->where('inicio', '<=', $request->end);
        }

        $eventos = $query->get()->map(function (RecursoReserva $reserva) {
            return [
                'id' => $reserva->id,
                'title' => $reserva->titulo ?: $reserva->recurso->nombre,
                'start' => $reserva->inicio->toIso8601String(),
                'end' => $reserva->fin->toIso8601String(),
                'extendedProps' => [
                    'recurso_id' => $reserva->recurso_id,
                    'user_id' => $reserva->user_id,
                    'comentario' => $reserva->comentario,
                    'estado' => $reserva->estado,
                    'color' => $reserva->recurso->color,
                ],
                'backgroundColor' => $reserva->recurso->color,
                'borderColor' => $reserva->recurso->color,
            ];
        });

        return response()->json($eventos);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $this->ensureModuleEnabled($user);

        $doctorId = $this->resolveDoctorId($user, $request->input('doctor_id'));

        $request->validate([
            'recurso_id' => ['required', 'exists:recursos,id'],
            'user_id' => ['required', 'exists:users,id'],
            'inicio' => ['required', 'date'],
            'duracion' => ['required', 'integer', 'min:1', 'max:1440'],
            'titulo' => ['nullable', 'string', 'max:255'],
            'comentario' => ['nullable', 'string'],
        ]);

        $recurso = Recurso::where('id', $request->recurso_id)
            ->where('user_id', $doctorId)
            ->firstOrFail();

        $responsable = User::where('id', $request->user_id)
            ->where(function ($q) use ($doctorId) {
                $q->where('id', $doctorId)->orWhere('created_by', $doctorId);
            })
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['doctor', 'asistente', 'secretaria']);
            })
            ->firstOrFail();

        if (!$responsable->hasRole('doctor') && !$responsable->hasPermissionTo(self::RECURSOS_PERMISSION)) {
            abort(403);
        }

        $inicio = Carbon::parse($request->inicio);
        $duracionMinutos = (int) $request->duracion;
        $fin = (clone $inicio)->addMinutes($duracionMinutos);

        Log::info('RecursoReserva store request', [
            'user_id' => $user->id,
            'doctor_id_param' => $request->input('doctor_id'),
            'recurso_id' => $request->input('recurso_id'),
            'responsable_id' => $request->input('user_id'),
            'inicio_raw' => $request->input('inicio'),
            'duracion_raw' => $request->input('duracion'),
            'inicio_final' => $inicio->toDateTimeString(),
            'fin_final' => $fin->toDateTimeString(),
        ]);

        $conflicto = RecursoReserva::where('recurso_id', $recurso->id)
            ->where('estado', 'activo')
            ->where(function ($q) use ($inicio, $fin) {
                $q->where('inicio', '<', $fin->toDateTimeString())
                    ->where('fin', '>', $inicio->toDateTimeString());
            })
            ->orderBy('inicio')
            ->first();

        if ($conflicto) {
            Log::info('RecursoReserva store conflicto', [
                'nuevo_inicio' => $inicio->toDateTimeString(),
                'nuevo_fin' => $fin->toDateTimeString(),
                'conflicto_id' => $conflicto->id,
                'conflicto_inicio' => $conflicto->inicio ? $conflicto->inicio->toDateTimeString() : null,
                'conflicto_fin' => $conflicto->fin ? $conflicto->fin->toDateTimeString() : null,
            ]);

            return response()->json([
                'message' => 'Este recurso ya está reservado.',
            ], 422);
        }

        $reserva = RecursoReserva::create([
            'recurso_id' => $recurso->id,
            'user_id' => $responsable->id,
            'titulo' => $request->titulo,
            'comentario' => $request->comentario,
            'inicio' => $inicio,
            'fin' => $fin,
            'estado' => 'activo',
        ]);

        return response()->json([
            'id' => $reserva->id,
        ], 201);
    }

    public function update(Request $request, RecursoReserva $reserva)
    {
        $user = Auth::user();

        $this->ensureModuleEnabled($user);

        $doctorId = $this->resolveDoctorId($user, $request->input('doctor_id'));

        if ($reserva->recurso->user_id !== $doctorId && !$user->hasRole('root')) {
            abort(403);
        }

        $request->validate([
            'inicio' => ['required', 'date'],
            'fin' => ['required', 'date', 'after:inicio'],
            'titulo' => ['nullable', 'string', 'max:255'],
            'comentario' => ['nullable', 'string'],
        ]);

        $inicio = Carbon::parse($request->inicio);
        $fin = Carbon::parse($request->fin);

        $conflicto = RecursoReserva::where('recurso_id', $reserva->recurso_id)
            ->where('id', '!=', $reserva->id)
            ->where('estado', 'activo')
            ->where(function ($q) use ($inicio, $fin) {
                $q->where('inicio', '<', $fin->toDateTimeString())
                    ->where('fin', '>', $inicio->toDateTimeString());
            })
            ->orderBy('inicio')
            ->first();

        if ($conflicto) {
            Log::info('RecursoReserva update conflicto', [
                'reserva_id' => $reserva->id,
                'nuevo_inicio' => $inicio->toDateTimeString(),
                'nuevo_fin' => $fin->toDateTimeString(),
                'conflicto_id' => $conflicto->id,
                'conflicto_inicio' => $conflicto->inicio ? $conflicto->inicio->toDateTimeString() : null,
                'conflicto_fin' => $conflicto->fin ? $conflicto->fin->toDateTimeString() : null,
            ]);

            return response()->json([
                'message' => 'Este recurso ya está reservado.',
            ], 422);
        }

        $reserva->update([
            'inicio' => $inicio,
            'fin' => $fin,
            'titulo' => $request->titulo,
            'comentario' => $request->comentario,
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function destroy(Request $request, RecursoReserva $reserva)
    {
        $user = Auth::user();

        $this->ensureModuleEnabled($user);

        $doctorId = $this->resolveDoctorId($user, $request->input('doctor_id'));

        if ($reserva->recurso->user_id !== $doctorId && !$user->hasRole('root')) {
            abort(403);
        }

        $reserva->delete();

        return response()->json(['status' => 'ok']);
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
            if (!$ownerId) {
                abort(403);
            }
            $owner = User::find($ownerId);
            if ($owner && $this->subscriptionService->hasActiveFeature($owner, 'clinica') && $user->hasPermissionTo(self::RECURSOS_PERMISSION)) {
                return;
            }
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
