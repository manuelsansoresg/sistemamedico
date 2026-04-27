<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Configuracion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        if (! $user->hasRole('doctor') && ! $user->hasRole('root')) {
            abort(403);
        }

        $configuracion = Configuracion::firstOrCreate(
            ['user_id' => $user->id],
            ['created_by' => $user->id]
        );

        return view('admin.branding.edit', compact('configuracion'));
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        if (! $user->hasRole('doctor') && ! $user->hasRole('root')) {
            abort(403);
        }

        $configuracion = Configuracion::firstOrCreate(
            ['user_id' => $user->id],
            ['created_by' => $user->id]
        );

        $file = $request->file('logo');
        $path = $file->store('branding-logos', 'public');

        if ($configuracion->branding_logo_path) {
            Storage::disk('public')->delete($configuracion->branding_logo_path);
        }

        $configuracion->branding_logo_path = $path;
        $configuracion->save();

        try {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'actualizar_branding_logo',
                'section' => 'branding',
                'model_type' => Configuracion::class,
                'model_id' => $configuracion->id,
                'payload' => [
                    'logo_path' => $path,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('No se pudo registrar auditoría actualizar_branding_logo', ['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('branding.edit')
            ->with('success', __('branding.logo_updated'));
    }
}
