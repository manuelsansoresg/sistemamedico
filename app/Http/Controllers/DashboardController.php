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
            // Get today's appointments
            $today = Carbon::today();
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

            return view('doctor.dashboard', compact('citasHoy', 'pendientes'));
        }

        return view('dashboard');
    }
}
