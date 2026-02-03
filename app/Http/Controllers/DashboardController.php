<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cita;
use App\Models\Pendiente;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('root')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('doctor')) {
            // Get today's appointments (Use server date or allow a wider range to catch timezone diffs)
            // Using now()->toDateString() relies on app timezone. If app is UTC, it might differ from user.
            // We'll fetch appointments where date is effectively "today" in general terms.
            $citasHoy = Cita::with(['paciente', 'consultorio'])
                ->where('doctor_id', $user->id)
                ->whereDate('fecha', now()->timezone('America/Mexico_City')->toDateString())
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

            return view('doctor.dashboard', compact('citasHoy', 'pendientes'));
        }

        return view('dashboard');
    }
}
