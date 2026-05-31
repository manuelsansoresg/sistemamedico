<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSharedExpedientePermissionRequest;
use App\Http\Requests\UpdateSharedExpedientePermissionRequest;
use App\Models\SharedExpedienteAuditLog;
use App\Models\SharedExpedientePermission;
use App\Models\SharedExpedientePermissionAcceptance;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SharedExpedientePermissionController extends Controller
{
    public function store(StoreSharedExpedientePermissionRequest $request): RedirectResponse
    {
        /** @var \App\Models\User $patient */
        $patient = Auth::user();
        $doctor = $this->resolveDoctor($request);
        $durationHours = (int) ($request->integer('duration_hours') ?: 5);
        $terms = __('pacientes.qr.permissions.patient_terms');
        $temporaryAccessCode = $doctor ? null : $this->generateTemporaryAccessCode();

        $permission = SharedExpedientePermission::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor?->id,
            'especialidad_id' => $request->input('especialidad_id'),
            'permission_type' => $request->input('permission_type'),
            'can_edit_owned_records' => $doctor !== null && $request->boolean('can_edit_owned_records'),
            'status' => SharedExpedientePermission::STATUS_ACTIVE,
            'doctor_search_text' => $request->input('doctor_search'),
            'external_doctor_name' => $doctor ? null : $request->input('doctor_search'),
            'temporary_access_code' => $temporaryAccessCode,
            'starts_at' => now(),
            'expires_at' => now()->addHours($durationHours),
            'patient_terms_accepted_at' => now(),
            'patient_terms_hash' => hash('sha256', $terms),
            'notes' => $request->input('notes'),
        ]);

        $this->recordAcceptance($permission, $patient, 'patient', 'patient_share_terms', $terms, $request);
        $this->log($permission, $patient, 'created', $request, [
            'permission_type' => $permission->permission_type,
            'can_edit_owned_records' => $permission->can_edit_owned_records,
            'duration_hours' => $durationHours,
            'doctor_exists' => $doctor !== null,
            'temporary_access_code_generated' => $temporaryAccessCode !== null,
        ]);

        if ($doctor) {
            $doctor->notify(new SystemNotification(
                __('pacientes.qr.permissions.notifications.title'),
                __('pacientes.qr.permissions.notifications.message', ['patient' => $patient->name]),
                route('pacientes.shared.index', ['paciente_id' => $patient->id]),
                'fa-qrcode'
            ));
        }

        return back()->with('success', __('pacientes.qr.permissions.messages.created'));
    }

    public function update(UpdateSharedExpedientePermissionRequest $request, SharedExpedientePermission $permission): RedirectResponse
    {
        $durationHours = (int) ($request->integer('duration_hours') ?: 5);
        $terms = __('pacientes.qr.permissions.patient_terms');

        $permission->update([
            'permission_type' => $request->input('permission_type'),
            'can_edit_owned_records' => $permission->doctor_id !== null && $request->boolean('can_edit_owned_records'),
            'status' => SharedExpedientePermission::STATUS_ACTIVE,
            'expires_at' => now()->addHours($durationHours),
            'revoked_at' => null,
            'patient_terms_accepted_at' => now(),
            'patient_terms_hash' => hash('sha256', $terms),
            'notes' => $request->input('notes'),
        ]);

        /** @var \App\Models\User $patient */
        $patient = Auth::user();
        $this->recordAcceptance($permission, $patient, 'patient', 'patient_update_terms', $terms, $request);
        $this->log($permission, $patient, 'updated', $request, [
            'permission_type' => $permission->permission_type,
            'can_edit_owned_records' => $permission->can_edit_owned_records,
            'duration_hours' => $durationHours,
        ]);

        return back()->with('success', __('pacientes.qr.permissions.messages.updated'));
    }

    public function revoke(Request $request, SharedExpedientePermission $permission): RedirectResponse
    {
        /** @var \App\Models\User $patient */
        $patient = Auth::user();

        abort_unless($patient->hasRole('paciente') && $permission->patient_id === $patient->id, 403);

        $permission->update([
            'status' => SharedExpedientePermission::STATUS_REVOKED,
            'revoked_at' => now(),
        ]);

        $this->log($permission, $patient, 'revoked', $request);

        return back()->with('success', __('pacientes.qr.permissions.messages.revoked'));
    }

    private function resolveDoctor(StoreSharedExpedientePermissionRequest $request): ?User
    {
        if ($request->filled('doctor_id')) {
            $doctor = User::role('doctor')->find($request->integer('doctor_id'));

            if (! $doctor) {
                throw ValidationException::withMessages([
                    'doctor_id' => __('pacientes.qr.permissions.validation.doctor_not_found'),
                ]);
            }

            return $doctor;
        }

        if (! $request->filled('doctor_search')) {
            return null;
        }

        $search = trim((string) $request->input('doctor_search'));

        return User::role('doctor')
            ->where(function ($query) use ($search) {
                $query->where('email', $search)
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('apellido_materno', 'like', "%{$search}%");
            })
            ->first();
    }

    private function recordAcceptance(SharedExpedientePermission $permission, User $user, string $actorRole, string $termsKey, string $terms, Request $request): void
    {
        SharedExpedientePermissionAcceptance::updateOrCreate(
            [
                'shared_expediente_permission_id' => $permission->id,
                'user_id' => $user->id,
                'actor_role' => $actorRole,
            ],
            [
                'terms_key' => $termsKey,
                'terms_hash' => hash('sha256', $terms),
                'accepted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );
    }

    private function log(SharedExpedientePermission $permission, User $actor, string $action, Request $request, array $payload = []): void
    {
        SharedExpedienteAuditLog::create([
            'shared_expediente_permission_id' => $permission->id,
            'patient_id' => $permission->patient_id,
            'doctor_id' => $permission->doctor_id,
            'actor_id' => $actor->id,
            'actor_role' => $actor->roles->pluck('name')->first(),
            'action' => $action,
            'payload' => $payload,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function generateTemporaryAccessCode(): string
    {
        return Str::upper(Str::random(4)).'-'.random_int(1000, 9999);
    }
}
