<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Clinica;
use App\Models\Consultorio;
use App\Models\Horario;
use App\Models\Plantilla;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WizardController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function index()
    {
        $user = Auth::user();

        // Calcular límites usando el servicio
        $limites = $this->subscriptionService->calculateLimits($user);

        // Contadores actuales
        $actuales = [
            'clinicas' => Clinica::where('created_by', $user->id)->count(),
            'consultorios' => Consultorio::where('created_by', $user->id)->count(),
            'usuarios' => User::where('created_by', $user->id)
                ->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['asistente', 'secretaria']);
                })->count(),
            'horarios' => Horario::where('user_id', $user->id)->count(),
            'plantillas' => Plantilla::where('user_id', $user->id)->count(),
            'pacientes' => User::role('paciente')->where('created_by', $user->id)->count(),
        ];

        return view('doctor.wizard.index', compact('limites', 'actuales'));
    }

    public function storeClinica(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Validar límite
        if (! $this->subscriptionService->canCreate($user, 'clinica')) {
            return response()->json([
                'success' => false,
                'message' => 'Ha alcanzado el límite de clínicas permitidas por su suscripción.',
            ], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'telefono' => 'required|string|max:20',
            'ubicacion' => 'nullable|string',
            'logotipo' => 'nullable|image|max:2048', // 2MB Max
        ]);

        $logotipoPath = null;
        if ($request->hasFile('logotipo')) {
            $file = $request->file('logotipo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('clinicas'), $filename);
            $logotipoPath = 'clinicas/'.$filename;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $activo = $user->hasRole('root') ? $request->boolean('activo') : true;

        $clinica = Clinica::create([
            'nombre' => $request->nombre,
            'direccion' => $request->direccion,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'telefono' => $request->telefono,
            'ubicacion' => $request->ubicacion,
            'logotipo' => $logotipoPath,
            'created_by' => Auth::id(),
            'activo' => $activo,
        ]);

        if ($user && $user->hasRole('doctor')) {
            $clinica->users()->syncWithoutDetaching([$user->id]);
        }

        return response()->json([
            'success' => true,
            'actuales' => [
                'clinicas' => Clinica::where('created_by', Auth::id())->count(),
            ],
        ]);
    }

    public function storeConsultorio(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Validar límite
        if (! $this->subscriptionService->canCreate($user, 'consultorio')) {
            return response()->json([
                'success' => false,
                'message' => 'Ha alcanzado el límite de consultorios permitidos por su suscripción.',
            ], 403);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $activo = $user->hasRole('root') ? $request->boolean('activo') : true;

        $consultorio = Consultorio::create([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'created_by' => Auth::id(),
            'activo' => $activo,
        ]);

        if ($user && $user->hasRole('doctor')) {
            $consultorio->users()->syncWithoutDetaching([$user->id]);
        }

        return response()->json([
            'success' => true,
            'actuales' => [
                'consultorios' => Consultorio::where('created_by', Auth::id())->count(),
            ],
        ]);
    }
}
