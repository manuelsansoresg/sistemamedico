<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PatientEmpathyNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class PatientEmpathyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:doctor', 'check.doctor.status']);
    }

    public function index(Request $request, User $paciente)
    {
        /** @var \App\Models\User $doctor */
        $doctor = Auth::user();

        $this->authorizeDoctorPatient($doctor, $paciente);

        $query = PatientEmpathyNote::query()
            ->where('patient_id', $paciente->id)
            ->where('doctor_id', $doctor->id);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('content', 'like', "%{$search}%");
        }

        $notes = $query->latest('created_at')
            ->paginate(10);

        return response()->json([
            'data' => collect($notes->items())->map(function (PatientEmpathyNote $note) {
                return [
                    'id' => $note->id,
                    'content' => $note->content,
                    'created_at' => $note->created_at?->format('d/m/Y H:i'),
                    'month_year' => $note->created_at?->translatedFormat('F Y'),
                ];
            })->values(),
            'current_page' => $notes->currentPage(),
            'last_page' => $notes->lastPage(),
            'has_more' => $notes->hasMorePages(),
        ]);
    }

    public function store(Request $request, User $paciente)
    {
        /** @var \App\Models\User $doctor */
        $doctor = Auth::user();

        $this->authorizeDoctorPatient($doctor, $paciente);

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $note = PatientEmpathyNote::create([
            'patient_id' => $paciente->id,
            'doctor_id' => $doctor->id,
            'content' => $validated['content'],
        ]);

        try {
            AuditLog::create([
                'user_id' => $doctor->id,
                'action' => 'crear_nota_empatia',
                'section' => 'pacientes',
                'model_type' => get_class($note),
                'model_id' => $note->id,
                'payload' => [
                    'patient_id' => $paciente->id,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('No se pudo registrar auditoría crear_nota_empatia', [
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'id' => $note->id,
        ], 201);
    }

    public function update(Request $request, PatientEmpathyNote $note)
    {
        /** @var \App\Models\User $doctor */
        $doctor = Auth::user();

        if ((int) $note->doctor_id !== (int) $doctor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $note->update([
            'content' => $validated['content'],
        ]);

        try {
            AuditLog::create([
                'user_id' => $doctor->id,
                'action' => 'editar_nota_empatia',
                'section' => 'pacientes',
                'model_type' => get_class($note),
                'model_id' => $note->id,
                'payload' => [
                    'patient_id' => $note->patient_id,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('No se pudo registrar auditoría editar_nota_empatia', [
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function destroy(PatientEmpathyNote $note)
    {
        /** @var \App\Models\User $doctor */
        $doctor = Auth::user();

        if ((int) $note->doctor_id !== (int) $doctor->id) {
            abort(403);
        }

        $patientId = (int) $note->patient_id;
        $noteId = (int) $note->id;

        $note->delete();

        try {
            AuditLog::create([
                'user_id' => $doctor->id,
                'action' => 'borrar_nota_empatia',
                'section' => 'pacientes',
                'model_type' => PatientEmpathyNote::class,
                'model_id' => $noteId,
                'payload' => [
                    'patient_id' => $patientId,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('No se pudo registrar auditoría borrar_nota_empatia', [
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function authorizeDoctorPatient(User $doctor, User $paciente): void
    {
        if (! $paciente->hasRole('paciente')) {
            abort(403);
        }

        $linked = $paciente->doctors()->where('users.id', $doctor->id)->exists();
        $created = (int) $paciente->created_by === (int) $doctor->id;

        if (! $linked && ! $created) {
            abort(403);
        }
    }
}
