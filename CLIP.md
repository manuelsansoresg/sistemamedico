<?php

namespace App\Lib;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class PayClipService
{
    protected string $apiUrl;
    protected string $authToken;

    public function __construct()
    {
        $this->apiUrl = config('services.payclip.api_url', 'https://api.payclip.com');
        $this->authToken = env('PAYCLIP_AUTH_TOKEN');
    }

    /**
     * Procesa un pago mediante PayClip
     * 
     * @param float $amount Monto del pago
     * @param string $currency Moneda (por defecto MXN)
     * @param string $description Descripción del pago
     * @param string $paymentToken Token del método de pago
     * @param string $email Correo electrónico del cliente
     * @param string $phone Teléfono del cliente
     * @return array Respuesta de la API de PayClip
     */
    public function processPayment(
        float $amount,
        string $paymentToken,
        string $email,
        string $phone,
        string $description = 'Pago de servicio',
        string $currency = 'MXN'
    ) {
        //dd($this->authToken);
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->authToken,
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/payments", [
            'amount' => $amount,
            'currency' => $currency,
            'description' => $description,
            'payment_method' => [
                'token' => $paymentToken
            ],
            'customer' => [
                'email' => $email,
                'phone' => $phone
            ]
        ]);
        $responseData = $response->json();
        $status = $responseData['status'] ?? null;
        return [
            'response' => $responseData,
            'status' => $status
        ];
    }
}

///////////////
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClipPaymentController extends Controller
{
    protected $clipApiUrl = 'https://api.payclip.com/v2';
    
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('CLIP_API_KEY');
    }

    /**
     * Muestra la vista para iniciar el pago con Clip
     */
    public function showPaymentForm()
    {
        return view('payments.clip-payment');
    }

    /**
     * Inicia una transacción con Clip
     */
    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'email' => 'required|email',
            'name' => 'required|string|max:255',
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
                'Content-Type' => 'application/json',
            ])->post($this->clipApiUrl . '/charges', [
                'amount' => $validated['amount'] * 100, // Clip espera el monto en centavos
                'currency' => 'MXN',
                'description' => $validated['description'],
                'customer' => [
                    'email' => $validated['email'],
                    'name' => $validated['name'],
                ],
                'return_url' => route('clip.payment.callback'),
                'cancel_url' => route('clip.payment.cancel'),
            ]);

            $responseData = $response->json();

            if ($response->successful()) {
                // Guardar información de la transacción en la base de datos si es necesario
                // ...

                // Redirigir al usuario a la página de pago de Clip
                return redirect($responseData['payment_url']);
            } else {
                return back()->withErrors(['message' => 'Error al procesar el pago: ' . ($responseData['message'] ?? 'Error desconocido')]);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error al conectar con Clip: ' . $e->getMessage()]);
        }
    }

    /**
     * Callback para procesar la respuesta de pago exitoso de Clip
     */
    public function handleCallback(Request $request)
    {
        // Verificar la transacción con Clip
        $paymentId = $request->input('payment_id');

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
            ])->get($this->clipApiUrl . '/charges/' . $paymentId);

            $paymentData = $response->json();

            if ($response->successful() && isset($paymentData['status']) && $paymentData['status'] === 'paid') {
                // Actualizar el estado del pago en la base de datos
                // ...

                return view('payments.success', ['paymentData' => $paymentData]);
            } else {
                return view('payments.failed', ['error' => 'La verificación del pago falló']);
            }
        } catch (\Exception $e) {
            return view('payments.failed', ['error' => 'Error al verificar el pago: ' . $e->getMessage()]);
        }
    }

    /**
     * Maneja la cancelación del pago por parte del usuario
     */
    public function handleCancellation()
    {
        return view('payments.cancelled');
    }
}
/////////
<?php

namespace App\Models;

use App\Lib\PayClipService;
use App\Models\User;
use App\Models\VinculacionSolicitud;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'card_token_id',
        'solicitud_id',
        'user_id',
        'status',
        'amount',
        'currency',
        'description',
    ];

    public static function savePayment($dataPayment, $solicitudId = null)
    {
        try {
            Log::info('Iniciando Payment::savePayment con datos:', $dataPayment);
            $user = User::find($dataPayment['user_id']);
            $payment = new PayClipService();
            Log::info('PayClipService creado, procesando pago...');
            
            $getPayment = $payment->processPayment($dataPayment['amount'], $dataPayment['card_token_id'], $user->email, $user->ttelefono, $dataPayment['description']);
            Log::info('Respuesta de PayClip:', $getPayment);
            
            $status = $getPayment['status'] == 'approved' ? 1 : 0;
            Log::info('Status del pago:', ['status' => $status, 'original_status' => $getPayment['status']]);
            
            if ($status == 1) {
                Log::info('Pago aprobado, verificando sesión activa...');
                // Solo ejecutar getUsedStatusPackages si hay sesión activa
                if (Auth::check()) {
                    Log::info('Sesión activa, obteniendo status packages...');
                    
                    try {
                        $statusPackages = Solicitud::getUsedStatusPackages();
                        Log::info('Status packages obtenido:', $statusPackages);
                    
                        if ($statusPackages && isset($statusPackages['totalUsuariosSistema']['solicitudId'])) {
                            $solicitudId = $statusPackages['totalUsuariosSistema']['solicitudId'];
                            Log::info('Guardando vinculación con solicitudId:', ['solicitudId' => $solicitudId, 'user_id' => $dataPayment['user_id']]);
                            VinculacionSolicitud::saveVinculacion($dataPayment['user_id'], $solicitudId); 
                        } else {
                            Log::warning('No se pudo obtener solicitudId para vinculación');
                        }
                    } catch (\Exception $e) {
                        Log::error('Error en Solicitud::getUsedStatusPackages: ' . $e->getMessage());
                        $statusPackages = null;
                    }

                  
                } else {
                    VinculacionSolicitud::saveVinculacion($dataPayment['user_id'], $solicitudId, $dataPayment['user_id']); 
                }


            }
            
            Log::info('Creando registro de Payment...');
            $payment = Payment::create([
                'card_token_id' => $dataPayment['card_token_id'],
                'solicitud_id' => $dataPayment['solicitud_id'],
                'user_id' => $dataPayment['user_id'],
                'status' => $status,
                'amount' => $dataPayment['amount'],
                'currency' => 'MXN',
                'description' => $dataPayment['description'],
            ]);
            
            Log::info('Payment creado exitosamente:', $payment->toArray());
            return $getPayment;
        } catch (\Exception $e) {
            Log::error('Error en Payment::savePayment: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
