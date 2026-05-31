<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PatientQrController extends Controller
{
    public function show(User $paciente): View
    {
        $this->authorizePatientAccess($paciente);

        $publicUrl = route('public.expediente.show', $paciente->ensurePublicExpedienteToken());
        $qrDataUri = $this->buildQrDataUri($publicUrl);
        $layout = Auth::user()?->hasRole('paciente') ? 'app-layout' : 'admin-layout';
        $permissions = $paciente->sharedExpedientePermissions()
            ->with(['doctor', 'especialidad'])
            ->latest()
            ->get();
        $activePermissionCount = $permissions->filter->isActive()->count();
        $especialidades = Especialidad::query()->where('activo', true)->orderBy('nombre')->get();

        return view('admin.pacientes.qr', compact('paciente', 'publicUrl', 'qrDataUri', 'layout', 'permissions', 'activePermissionCount', 'especialidades'));
    }

    public function searchDoctors(Request $request): JsonResponse
    {
        /** @var \App\Models\User $paciente */
        $paciente = Auth::user();
        abort_unless($paciente instanceof User && $paciente->hasRole('paciente') && $paciente->perfil_compartido, 403);

        $search = trim((string) $request->query('q', ''));

        if (mb_strlen($search) < 3) {
            return response()->json([]);
        }

        $doctors = User::role('doctor')
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                    ->orWhere('apellido_materno', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'apellido_paterno', 'apellido_materno', 'email'])
            ->map(fn (User $doctor): array => [
                'id' => $doctor->id,
                'label' => trim("{$doctor->name} {$doctor->apellido_paterno} {$doctor->apellido_materno}"),
                'email' => $doctor->email,
            ]);

        return response()->json($doctors);
    }

    public function showMine(): View
    {
        /** @var \App\Models\User $paciente */
        $paciente = Auth::user();

        return $this->show($paciente);
    }

    public function regenerate(User $paciente): RedirectResponse
    {
        $user = Auth::user();

        if (! $user instanceof User || ! $user->hasRole('paciente') || $user->id !== $paciente->id) {
            abort(403);
        }

        abort_unless($paciente->perfil_compartido, 403);

        $paciente->regeneratePublicExpedienteToken();

        return back()->with('success', __('pacientes.qr.messages.regenerated'));
    }

    public function regenerateMine(): RedirectResponse
    {
        /** @var \App\Models\User $paciente */
        $paciente = Auth::user();

        return $this->regenerate($paciente);
    }

    private function buildQrDataUri(string $publicUrl): string
    {
        return (new Builder(
            writer: new SvgWriter,
            data: $publicUrl,
            size: 280,
            margin: 12,
        ))->build()->getDataUri();
    }

    private function authorizePatientAccess(User $paciente): void
    {
        abort_unless($paciente->hasRole('paciente'), 404);
        abort_unless($paciente->perfil_compartido, 403);

        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        if ($user->hasRole('paciente') && $user->id === $paciente->id) {
            return;
        }

        abort(403);
    }
}
