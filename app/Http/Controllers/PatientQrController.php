<?php

namespace App\Http\Controllers;

use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
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

        return view('admin.pacientes.qr', compact('paciente', 'publicUrl', 'qrDataUri', 'layout'));
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

        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        if ($user->hasRole('paciente') && $user->id === $paciente->id) {
            return;
        }

        if ($user->hasRole('root')) {
            return;
        }

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            $ownerId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $hasAccess = $paciente->created_by === $ownerId || $paciente->doctors()->where('users.id', $ownerId)->exists();

            if ($hasAccess) {
                return;
            }
        }

        abort(403);
    }
}
