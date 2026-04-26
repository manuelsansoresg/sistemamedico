<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Clinica;
use App\Models\Consultorio;
use App\Models\DiaSinCita;
use App\Models\Pendiente;
use App\Models\Suscripcion;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasRole('root')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('paciente')) {
            $query = \App\Models\Consulta::with(['cita.clinica', 'cita.consultorio', 'doctor'])
                ->join('citas', 'consultas.cita_id', '=', 'citas.id')
                ->select('consultas.*')
                ->where('consultas.paciente_id', $user->id);

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

            $query->orderBy('citas.fecha', 'desc');
            $expedientes = $query->paginate(15)->withQueryString();

            $clinicas = Clinica::whereIn('id', function ($q) use ($user) {
                $q->select('clinica_id')
                    ->from('citas')
                    ->whereIn('id', function ($q2) use ($user) {
                        $q2->select('cita_id')->from('consultas')->where('paciente_id', $user->id);
                    });
            })->get();

            $consultorios = Consultorio::whereIn('id', function ($q) use ($user) {
                $q->select('consultorio_id')
                    ->from('citas')
                    ->whereIn('id', function ($q2) use ($user) {
                        $q2->select('cita_id')->from('consultas')->where('paciente_id', $user->id);
                    });
            })->get();

            return view('paciente.dashboard', compact('expedientes', 'clinicas', 'consultorios'));
        }

        if ($user->hasRole(['doctor', 'asistente', 'secretaria'])) {
            // Determine scope (Doctor ID)
            $doctorId = $user->hasRole('doctor') ? $user->id : $user->created_by;
            $doctor = $user->hasRole('doctor') ? $user : \App\Models\User::find($doctorId);

            // Get today's appointments (Use server date or allow a wider range to catch timezone diffs)
            // Using now()->toDateString() relies on app timezone. If app is UTC, it might differ from user.
            // We'll fetch appointments where date is effectively "today" in general terms.
            $today = now()->timezone('America/Mexico_City')->toDateString();

            $citasHoy = Cita::with(['paciente', 'consultorio'])
                ->where('doctor_id', $doctorId)
                ->whereDate('fecha', $today)
                ->where('estado', '!=', 'cancelada')
                ->orderBy('hora_inicio')
                ->take(5) // Limit for preview
                ->get();

            // Get pending reminders (Scoped to Doctor)
            $pendientes = Pendiente::where('user_id', $doctorId)
                ->where('activo', true)
                ->orderBy('fecha')
                ->orderBy('hora')
                ->take(5)
                ->get();

            // Check for blocked days
            // Get consultorio IDs from the Doctor context
            if ($doctor) {
                $assignedIds = $doctor->consultorios()->pluck('consultorios.id')->toArray();
                $createdIds = \App\Models\Consultorio::where('created_by', $doctorId)->pluck('id')->toArray();
                $userConsultorioIds = array_unique(array_merge($assignedIds, $createdIds));

                $diasBloqueadosHoy = DiaSinCita::whereDate('fecha_inicio', '<=', $today)
                    ->whereDate('fecha_fin', '>=', $today)
                    ->whereHas('consultorios', function ($q) use ($userConsultorioIds) {
                        $q->whereIn('consultorios.id', $userConsultorioIds);
                    })
                    ->with('consultorios')
                    ->get();
            } else {
                $diasBloqueadosHoy = collect();
            }

            $notifications = $user->unreadNotifications()
                ->where('type', 'App\Notifications\SubscriptionExpiringNotification')
                ->get();

            $canSharePacientes = $doctor ? $this->subscriptionService->hasActiveFeature($doctor, 'paciente') : false;

            // Cedula validation requirement based on active packages
            $requiresCedulaValidation = Suscripcion::where('user_id', $user->id)
                ->where('tipo', 'paquete')
                ->pagadaVigente()
                ->with('paquete')
                ->get()
                ->contains(function ($s) {
                    return optional($s->paquete)->validar_cedula === true;
                });

            $cedulaStatus = $user->estatus_cedula ?? 'na';

            return view('doctor.dashboard', compact('citasHoy', 'pendientes', 'diasBloqueadosHoy', 'notifications', 'canSharePacientes', 'requiresCedulaValidation', 'cedulaStatus'));
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
