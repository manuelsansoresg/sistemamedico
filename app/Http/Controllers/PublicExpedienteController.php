<?php

namespace App\Http\Controllers;

use App\Models\Clinica;
use App\Models\Consulta;
use App\Models\Consultorio;
use App\Models\SharedExpedienteAuditLog;
use App\Models\SharedExpedientePermission;
use App\Models\SharedExpedientePermissionAcceptance;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PublicExpedienteController extends Controller
{
    public function show(string $token, Request $request): View
    {
        $paciente = $this->findPatientByToken($token);
        $permission = $this->resolvePermissionCandidate($paciente, $request);

        if (! $permission) {
            return view('public.expediente-access', compact('paciente', 'token'));
        }

        if (! $this->hasAcceptedTerms($permission, $request)) {
            if ($request->boolean('accept_terms')) {
                $this->recordAccessAcceptance($permission, $request);
            } else {
                return view('public.expediente-access', compact('paciente', 'token', 'permission'));
            }
        }

        $this->logAccess($permission, $request, 'viewed');

        $query = Consulta::with(['cita.clinica', 'cita.consultorio', 'doctor', 'plantilla', 'estudios'])
            ->join('citas', 'consultas.cita_id', '=', 'citas.id')
            ->select('consultas.*')
            ->where('consultas.paciente_id', $paciente->id);

        if ($request->filled('clinica_id')) {
            $query->where('citas.clinica_id', $request->clinica_id);
        }

        if ($request->filled('consultorio_id')) {
            $query->where('citas.consultorio_id', $request->consultorio_id);
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('citas.fecha', '>=', $request->fecha_inicio);
        }

        if ($request->filled('fecha_fin')) {
            $query->whereDate('citas.fecha', '<=', $request->fecha_fin);
        }

        $expedientes = $query->orderBy('citas.fecha', 'desc')->paginate(15)->withQueryString();
        $clinicas = $this->clinicasForPatient($paciente);
        $consultorios = $this->consultoriosForPatient($paciente);

        return view('public.expediente', compact('paciente', 'expedientes', 'clinicas', 'consultorios', 'token'));
    }

    public function consulta(string $token, Consulta $consulta): View
    {
        $paciente = $this->findPatientByToken($token);
        $permission = $this->resolvePermissionCandidate($paciente, request());

        if (! $permission || ! $this->hasAcceptedTerms($permission, request())) {
            abort(403);
        }

        $this->logAccess($permission, request(), 'viewed_consultation');

        abort_unless($consulta->paciente_id === $paciente->id, 404);

        $consulta->load([
            'paciente',
            'doctor',
            'cita.clinica',
            'cita.consultorio',
            'plantilla',
            'valores.campo',
            'estudios.archivos',
        ]);

        return view('public.consulta', compact('paciente', 'consulta', 'token'));
    }

    private function findPatientByToken(string $token): User
    {
        return User::role('paciente')
            ->where('patient_public_token', $token)
            ->where('perfil_compartido', true)
            ->whereHas('sharedExpedientePermissions', fn ($query) => $query->active())
            ->firstOrFail();
    }

    private function resolvePermissionCandidate(User $paciente, Request $request): ?SharedExpedientePermission
    {
        $permissions = $paciente->sharedExpedientePermissions()
            ->active()
            ->with(['doctor', 'especialidad'])
            ->get();

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user?->hasRole('doctor')) {
            $permission = $permissions->first(function (SharedExpedientePermission $permission) use ($user) {
                if ($permission->doctor_id === $user->id) {
                    return true;
                }

                return $permission->doctor_id === null
                    && $permission->especialidad_id !== null
                    && $permission->especialidad_id === $user->especialidad_id;
            });

            if ($permission) {
                return $permission;
            }
        }

        if ($request->filled('access_code')) {
            return $permissions->first(
                fn (SharedExpedientePermission $permission) => $permission->matchesTemporaryAccessCode($request->input('access_code'))
            );
        }

        return null;
    }

    private function hasAcceptedTerms(SharedExpedientePermission $permission, Request $request): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user?->hasRole('doctor')) {
            return $permission->acceptances()
                ->where('user_id', $user->id)
                ->where('actor_role', 'doctor')
                ->exists();
        }

        if ($request->filled('access_code')) {
            return $permission->acceptances()
                ->where('actor_role', 'external')
                ->exists();
        }

        return false;
    }

    private function recordAccessAcceptance(SharedExpedientePermission $permission, Request $request): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $actorRole = $user?->hasRole('doctor') ? 'doctor' : 'external';
        $terms = $actorRole === 'doctor'
            ? __('pacientes.qr.permissions.doctor_terms')
            : __('pacientes.qr.permissions.external_terms');

        SharedExpedientePermissionAcceptance::updateOrCreate(
            [
                'shared_expediente_permission_id' => $permission->id,
                'user_id' => $user?->id,
                'actor_role' => $actorRole,
            ],
            [
                'terms_key' => $actorRole.'_access_terms',
                'terms_hash' => hash('sha256', $terms),
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        $this->logAccess($permission, $request, 'accepted_terms');
    }

    private function logAccess(SharedExpedientePermission $permission, Request $request, string $action): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        SharedExpedienteAuditLog::create([
            'shared_expediente_permission_id' => $permission->id,
            'patient_id' => $permission->patient_id,
            'doctor_id' => $permission->doctor_id,
            'actor_id' => $user?->id,
            'actor_role' => $user?->roles->pluck('name')->first() ?? 'external',
            'action' => $action,
            'payload' => [
                'via' => $request->filled('access_code') ? 'temporary_access_code' : 'authenticated_user',
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function clinicasForPatient(User $paciente): Collection
    {
        return Clinica::whereIn('id', function ($q) use ($paciente) {
            $q->select('clinica_id')
                ->from('citas')
                ->whereIn('id', function ($q2) use ($paciente) {
                    $q2->select('cita_id')->from('consultas')->where('paciente_id', $paciente->id);
                });
        })->get();
    }

    private function consultoriosForPatient(User $paciente): Collection
    {
        return Consultorio::whereIn('id', function ($q) use ($paciente) {
            $q->select('consultorio_id')
                ->from('citas')
                ->whereIn('id', function ($q2) use ($paciente) {
                    $q2->select('cita_id')->from('consultas')->where('paciente_id', $paciente->id);
                });
        })->get();
    }
}
