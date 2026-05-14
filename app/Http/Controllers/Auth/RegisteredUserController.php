<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\BienvenidaTarjetaMail;
use App\Mail\InstruccionesTransferenciaMail;
use App\Models\Especialidad;
use App\Models\Paquete;
use App\Models\Suscripcion;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $paquetes = Paquete::where('activo', true)
            ->with(['catalogos' => function ($q) {
                $q->select('catalogos.id', 'catalogos.nombre');
            }])
            ->get();
        $especialidades = Especialidad::where('activo', true)->get();

        $terminosHtml = '';
        if (File::exists(base_path('terminos.md'))) {
            try {
                $terminosContent = file_get_contents(base_path('terminos.md'));
                $terminosHtml = Str::markdown($terminosContent);

                // Double check if markdown returned empty
                if (empty(trim($terminosHtml))) {
                    Log::warning('Markdown parsing returned empty string.');
                    $terminosHtml = nl2br(e($terminosContent));
                }
            } catch (\Exception $e) {
                Log::error('Markdown parsing failed: '.$e->getMessage());
                // Try reading again just in case
                $terminosContent = file_get_contents(base_path('terminos.md'));
                $terminosHtml = nl2br(e($terminosContent));
            }
        } else {
            $terminosHtml = '<p class="text-red-500">'.__('auth.registration.payment_errors.terms_load_failed').'</p>';
            Log::warning('Terminos file not found at: '.base_path('terminos.md'));
        }

        // Ensure we have something to show
        if (empty($terminosHtml)) {
            $terminosHtml = '<p>'.__('auth.registration.payment_errors.terms_unavailable').'</p>';
        }

        $clipApiKey = env('CLIP_API_KEY');

        return view('auth.register', compact('paquetes', 'especialidades', 'terminosHtml', 'clipApiKey'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'telefono' => ['nullable', 'string', 'max:20'],
            'tipo_registro' => ['required', 'in:doctor,otro'],
            'tipo_establecimiento' => ['required', 'in:clinica,consultorio'],
            'paquete_id' => ['required', 'exists:paquetes,id'],
            'cedula_profesional' => ['nullable', 'required_if:tipo_registro,doctor', 'string', 'max:50'],
            'especialidad_id' => ['nullable', 'required_if:tipo_registro,doctor', 'exists:especialidades,id'],
            'terms_accepted' => ['required', 'accepted'],
            'payment_method' => ['required', 'in:tarjeta,transferencia'],
        ]);

        $paquete = Paquete::find($request->paquete_id);

        if ($request->payment_method === 'tarjeta') {
            $cardToken = $request->input('card_token_id');

            if (! $cardToken) {
                $message = __('auth.registration.payment_errors.card_read_failed');

                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $message,
                        'errors' => [
                            'payment' => [$message],
                        ],
                    ], 422);
                }

                return back()
                    ->withErrors(['payment' => $message])
                    ->withInput();
            }

            try {
                $apiKey = env('CLIP_API_KEY');
                $baseUrl = rtrim(env('CLIP_BASE_URL', 'https://api.payclip.com'), '/');

                if ($apiKey && $cardToken) {
                    $response = Http::withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Basic '.base64_encode($apiKey.':'),
                    ])->post($baseUrl.'/payments', [
                        'amount' => (float) $paquete->precio,
                        'currency' => 'MXN',
                        'description' => __('auth.registration.payment.subscription_description', ['package' => $paquete->nombre]),
                        'capture_method' => 'automatic',
                        'payment_method' => [
                            'token' => $cardToken,
                        ],
                        'customer' => [
                            'email' => $request->email,
                            'phone' => $request->telefono,
                        ],
                    ]);

                    if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                        $data = $response->json();
                        $rawStatus = $data['status'] ?? null;
                        $status = is_string($rawStatus) ? strtolower($rawStatus) : $rawStatus;

                        $declinedStatuses = ['failed', 'declined', 'rejected', 'canceled', 'cancelled'];

                        if (! $status || ! in_array($status, $declinedStatuses, true)) {
                            $user = User::create([
                                'name' => $request->name,
                                'apellido_paterno' => $request->apellido_paterno,
                                'apellido_materno' => $request->apellido_materno,
                                'email' => $request->email,
                                'password' => Hash::make($request->password),
                                'telefono' => $request->telefono,
                                'cedula_profesional' => $request->cedula_profesional,
                                'especialidad_id' => $request->especialidad_id,
                            ]);

                            if ($request->tipo_registro === 'doctor') {
                                $user->assignRole('doctor');
                            }

                            $token = Str::random(64);

                            $suscripcion = Suscripcion::create([
                                'user_id' => $user->id,
                                'paquete_id' => $paquete->id,
                                'precio' => $paquete->precio,
                                'metodo_pago' => 'tarjeta',
                                'estatus_pago' => 'pagado',
                                'fecha_inicio' => now(),
                                'fecha_fin' => now()->addYear(),
                                'token_pago' => $token,
                            ]);

                            if ($request->tipo_registro === 'doctor') {
                                if (Str::contains(Str::lower($paquete->nombre), ['validar', 'cédula', 'cedula'])) {
                                    $user->estatus_cedula = 'pendiente';
                                } else {
                                    $user->estatus_cedula = 'na';
                                }
                                $user->save();
                            }

                            event(new Registered($user));

                            Auth::login($user);

                            try {
                                Mail::to($user)->send(new BienvenidaTarjetaMail($suscripcion, $user));
                            } catch (\Exception $e) {
                                Log::error('Error enviando correo de bienvenida tarjeta: '.$e->getMessage());
                            }

                            if ($request->expectsJson()) {
                                return response()->json([
                                    'status' => 'success',
                                    'message' => __('auth.registration.success.card_payment_done'),
                                    'redirect' => route('dashboard'),
                                ]);
                            }

                            return redirect()
                                ->route('dashboard')
                                ->with('payment_success', __('auth.registration.success.card_payment_done'));
                        }

                        Log::warning('Clip Payments unexpected status', ['data' => $data]);
                        $message = __('auth.registration.payment_errors.payment_rejected');

                        if ($request->expectsJson()) {
                            return response()->json([
                                'status' => 'error',
                                'message' => $message,
                                'errors' => [
                                    'payment' => [$message],
                                ],
                            ], 422);
                        }

                        return back()
                            ->withErrors(['payment' => $message])
                            ->withInput();
                    }

                    Log::error('Clip API Error: '.($response instanceof \Illuminate\Http\Client\Response ? $response->body() : 'No response'));
                    $message = __('auth.registration.payment_errors.card_processing_failed');

                    if ($request->expectsJson()) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $message,
                            'errors' => [
                                'payment' => [$message],
                            ],
                        ], 422);
                    }

                    return back()
                        ->withErrors(['payment' => $message])
                        ->withInput();
                }

                Log::warning('Missing API key or card token for Clip Transparent Checkout');
                $message = __('auth.registration.payment_errors.payment_config_unavailable');

                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $message,
                        'errors' => [
                            'payment' => [$message],
                        ],
                    ], 422);
                }

                return back()
                    ->withErrors(['payment' => $message])
                    ->withInput();
            } catch (\Exception $e) {
                Log::error('Clip Transparent Checkout Error: '.$e->getMessage());
                $message = __('auth.registration.payment_errors.card_processing_failed');

                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $message,
                        'errors' => [
                            'payment' => [$message],
                        ],
                    ], 422);
                }

                return back()
                    ->withErrors(['payment' => $message])
                    ->withInput();
            }
        }

        $user = User::create([
            'name' => $request->name,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
            'cedula_profesional' => $request->cedula_profesional,
            'especialidad_id' => $request->especialidad_id,
        ]);

        if ($request->tipo_registro === 'doctor') {
            $user->assignRole('doctor');
        }

        $token = Str::random(64);

        $suscripcion = Suscripcion::create([
            'user_id' => $user->id,
            'paquete_id' => $paquete->id,
            'precio' => $paquete->precio,
            'metodo_pago' => $request->payment_method,
            'estatus_pago' => 'pendiente',
            'token_pago' => $token,
        ]);

        if ($request->tipo_registro === 'doctor') {
            if (Str::contains(Str::lower($paquete->nombre), ['validar', 'cédula', 'cedula'])) {
                $user->estatus_cedula = 'pendiente';
            } else {
                $user->estatus_cedula = 'na';
            }
            $user->save();
        }

        event(new Registered($user));

        Auth::login($user);

        $urlSubirComprobante = route('suscripciones.subir_comprobante', ['token' => $token]);

        try {
            Mail::to($user)->send(new InstruccionesTransferenciaMail($suscripcion, $user, $urlSubirComprobante));
        } catch (\Exception $e) {
            Log::error('Error enviando correo de transferencia: '.$e->getMessage());
        }

        return view('auth.register_success_transfer', ['paquete' => $paquete]);
    }

    public function successCard()
    {
        $id = session('last_suscripcion_id');
        if ($id) {
            $suscripcion = Suscripcion::find($id);
            if ($suscripcion && $suscripcion->estatus_pago !== 'pagado') {
                $suscripcion->estatus_pago = 'pagado';
                $suscripcion->fecha_inicio = now();
                $suscripcion->save();
            }
        }

        return view('auth.register_success_card');
    }

    public function successTransfer()
    {
        return view('auth.register_success_transfer');
    }
}
