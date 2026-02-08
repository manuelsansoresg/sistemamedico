<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cita;
use App\Models\Pendiente;
use App\Models\DiaSinCita;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('root')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('doctor')) {
            // Get today's appointments (Use server date or allow a wider range to catch timezone diffs)
            // Using now()->toDateString() relies on app timezone. If app is UTC, it might differ from user.
            // We'll fetch appointments where date is effectively "today" in general terms.
            $today = now()->timezone('America/Mexico_City')->toDateString();
            
            $citasHoy = Cita::with(['paciente', 'consultorio'])
                ->where('doctor_id', $user->id)
                ->whereDate('fecha', $today)
                ->where('estado', '!=', 'cancelada')
                ->orderBy('hora_inicio')
                ->take(5) // Limit for preview
                ->get();

            // Get pending reminders
            $pendientes = Pendiente::where('user_id', $user->id)
                ->where('activo', true)
                ->orderBy('fecha')
                ->orderBy('hora')
                ->take(5)
                ->get();

            // Check for blocked days
            // Get consultorio IDs: both assigned (pivot) and created (created_by)
            $assignedIds = $user->consultorios()->pluck('consultorios.id')->toArray();
            $createdIds = \App\Models\Consultorio::where('created_by', $user->id)->pluck('id')->toArray();
            $userConsultorioIds = array_unique(array_merge($assignedIds, $createdIds));

            $diasBloqueadosHoy = DiaSinCita::whereDate('fecha_inicio', '<=', $today)
                ->whereDate('fecha_fin', '>=', $today)
                ->whereHas('consultorios', function($q) use ($userConsultorioIds) {
                    $q->whereIn('consultorios.id', $userConsultorioIds);
                })
                ->with('consultorios')
                ->get();

            $notifications = $user->unreadNotifications()
                ->where('type', 'App\Notifications\SubscriptionExpiringNotification')
                ->get();

            return view('doctor.dashboard', compact('citasHoy', 'pendientes', 'diasBloqueadosHoy', 'notifications'));
        }

        return view('dashboard');
    }

    public function markNotificationRead($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();
        return back();
    }
}
