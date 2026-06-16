<?php

namespace App\Services;

use App\Models\AiConfig;
use App\Models\AiUsageLog;
use App\Models\AuditAiLog;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    public const ACTION_SUMMARY = 'summary';

    public const ACTION_ASSISTANT = 'assistant';

    public const ACTION_NOTES = 'notes';

    public const ACTION_DIAGNOSIS = 'diagnosis';

    public const ACTION_PRESCRIPTION = 'prescription';

    public const ACTION_FIELD_SUGGEST = 'field_suggest';

    public const ACTION_STUDY_ORDER = 'study_order';

    public const PROVIDERS = [
        'openai' => [
            'base_url' => 'https://api.openai.com/v1',
            'models_endpoint' => '/chat/completions',
            'auth_header' => 'Authorization',
            'auth_prefix' => 'Bearer',
        ],
        'deepseek' => [
            'base_url' => 'https://api.deepseek.com/v1',
            'models_endpoint' => '/chat/completions',
            'auth_header' => 'Authorization',
            'auth_prefix' => 'Bearer',
        ],
        'qwen' => [
            'base_url' => 'https://dashscope-us.aliyuncs.com/compatible-mode/v1',
            'models_endpoint' => '/chat/completions',
            'auth_header' => 'Authorization',
            'auth_prefix' => 'Bearer',
        ],
        'anthropic' => [
            'base_url' => 'https://api.anthropic.com',
            'models_endpoint' => '/v1/messages',
            'auth_header' => 'x-api-key',
            'auth_prefix' => '',
        ],
    ];

    public function getConfig(): ?AiConfig
    {
        return AiConfig::global();
    }

    public function ensureConfigExists(): AiConfig
    {
        return AiConfig::ensureGlobalExists();
    }

    public function canUseAi(User $user, string $actionType): bool
    {
        $config = $this->getConfig();

        if (! $config || ! $config->enabled) {
            return false;
        }

        if (! $config->isEnabledFor($actionType)) {
            return false;
        }

        if (! $config->api_key) {
            return false;
        }

        $subscriptionService = app(SubscriptionService::class);

        return $subscriptionService->canUseAi($user);
    }

    public function sendRequest(User $user, string $actionType, array $messages, ?User $patient = null, array $options = []): array
    {
        if (! $this->canUseAi($user, $actionType)) {
            throw new Exception('IA no disponible para este usuario.');
        }

        $config = $this->getConfig();
        $model = $config->getModelFor($actionType);
        $provider = $config->provider;

        if (! $model) {
            throw new Exception("No hay modelo configurado para la acción: {$actionType}");
        }

        $response = $this->callProvider($provider, $config->api_key, $model, $messages, $options);

        $usageLog = $this->logUsage($user, $patient, $actionType, $provider, $model, $response, $messages);
        $this->logAudit($user, $actionType, $provider, $model, $patient);

        $subscriptionService = app(SubscriptionService::class);
        $subscriptionService->incrementAiUsage($user);

        $response['usage_log_id'] = $usageLog->id;

        return $response;
    }

    public function summarizeExpediente(User $doctor, User $patient, array $expedienteData): array
    {
        $systemPrompt = 'Eres un asistente médico profesional. Resume expedientes clínicos de forma breve, precisa y útil para un médico. Usa máximo 180 palabras. Prioriza antecedentes relevantes, motivos recurrentes, hallazgos, diagnósticos, tratamientos, alergias y pendientes. No inventes datos.';

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $this->buildExpedientePrompt($expedienteData)],
        ];

        return $this->sendRequest($doctor, self::ACTION_SUMMARY, $messages, $patient, [
            'max_tokens' => 700,
            'temperature' => 0.2,
        ]);
    }

    public function assistantChat(User $doctor, array $messages, ?User $patient = null): array
    {
        $systemPrompt = 'Eres un asistente médico profesional. Ayudas al doctor con información clínica, sugerencias y recordatorios. Siempre responde con base médica y ética profesional.';

        if ($patient) {
            $systemPrompt .= "\n\nEl paciente actual es: {$patient->nombre_completo}.";
        }

        $allMessages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $messages
        );

        return $this->sendRequest($doctor, self::ACTION_ASSISTANT, $allMessages, $patient);
    }

    public function generateClinicalNotes(User $doctor, array $consultaData, ?User $patient = null): array
    {
        $systemPrompt = 'Eres un asistente médico. Genera notas clínicas profesionales y estructuradas basadas en los datos proporcionados.';

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $this->buildNotesPrompt($consultaData)],
        ];

        return $this->sendRequest($doctor, self::ACTION_NOTES, $messages, $patient);
    }

    public function suggestDiagnosis(User $doctor, array $symptoms, ?User $patient = null): array
    {
        $systemPrompt = 'Eres un asistente médico especializado en diagnóstico diferencial. Basado en los síntomas proporcionados, sugiere posibles diagnósticos ordenados por probabilidad. Incluye siempre advertencia de que esto es una sugerencia y no reemplaza el juicio clínico.';

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Síntomas del paciente:\n".json_encode($symptoms, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)],
        ];

        return $this->sendRequest($doctor, self::ACTION_DIAGNOSIS, $messages, $patient);
    }

    public function suggestFieldContent(User $doctor, User $patient, string $fieldName, string $fieldType, array $context): array
    {
        $systemPrompt = 'Eres un asistente médico profesional. Completas campos de consultas médicas con información clínica relevante basada en el contexto del paciente. Sé preciso, conciso y profesional. No inventes datos.';

        $contextText = '';
        foreach ($context as $key => $value) {
            if (! empty($value)) {
                $label = ucwords(str_replace('_', ' ', $key));
                $contextText .= "{$label}: {$value}\n";
            }
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Completa el campo '{$fieldName}' (tipo: {$fieldType}) con la siguiente información del paciente:\n\n{$contextText}\n\nGenera contenido apropiado para este campo basándote en los datos proporcionados."],
        ];

        return $this->sendRequest($doctor, self::ACTION_FIELD_SUGGEST, $messages, $patient, [
            'max_tokens' => 500,
            'temperature' => 0.3,
        ]);
    }

    public function suggestStudyOrder(User $doctor, User $patient, array $consultaData): array
    {
        $systemPrompt = 'Eres un asistente médico especializado en prescripción de estudios clínicos. Basado en el diagnóstico y datos del paciente, sugiere una orden de estudios clínicos apropiada. Incluye estudios de laboratorio, imagenología y otros relevantes. Formato: lista clara de estudios con indicaciones específicas.';

        $dataText = '';
        foreach ($consultaData as $key => $value) {
            if (! empty($value)) {
                $label = ucwords(str_replace('_', ' ', $key));
                $dataText .= "{$label}: {$value}\n";
            }
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Genera una orden de estudios clínicos basada en los siguientes datos del paciente:\n\n{$dataText}\n\nIncluye estudios de laboratorio, imagenología y otros estudios relevantes con indicaciones específicas."],
        ];

        return $this->sendRequest($doctor, self::ACTION_STUDY_ORDER, $messages, $patient, [
            'max_tokens' => 800,
            'temperature' => 0.3,
        ]);
    }

    public function callProviderDirect(string $provider, string $apiKey, string $model, array $messages, array $options = []): array
    {
        return $this->callProvider($provider, $apiKey, $model, $messages, $options);
    }

    protected function callProvider(string $provider, string $apiKey, string $model, array $messages, array $options = []): array
    {
        $config = self::PROVIDERS[$provider] ?? null;
        $apiKey = trim($apiKey);
        $timeout = (int) ($options['timeout'] ?? 90);

        if (function_exists('set_time_limit')) {
            @set_time_limit($timeout + 15);
        }

        if (! $config) {
            throw new Exception("Proveedor no soportado: {$provider}");
        }

        $url = $config['base_url'].$config['models_endpoint'];

        $headers = [
            'Content-Type' => 'application/json',
        ];

        if ($config['auth_prefix']) {
            $headers[$config['auth_header']] = "{$config['auth_prefix']} {$apiKey}";
        } else {
            $headers[$config['auth_header']] = $apiKey;
        }

        if ($provider === 'anthropic') {
            $headers['anthropic-version'] = '2023-06-01';
            $body = [
                'model' => $model,
                'max_tokens' => $options['max_tokens'] ?? 4096,
                'messages' => $this->convertToAnthropicMessages($messages),
                'system' => $messages[0]['content'] ?? '',
            ];
            if (count($messages) > 1) {
                $body['messages'] = array_slice($this->convertToAnthropicMessages($messages), 1);
            }
        } else {
            $body = [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'] ?? 4096,
                'temperature' => $options['temperature'] ?? 0.7,
            ];
        }

        $response = Http::withHeaders($headers)
            ->connectTimeout(15)
            ->timeout($timeout)
            ->post($url, $body);

        if (! $response->successful()) {
            Log::error('AI provider error', [
                'provider' => $provider,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new Exception($this->buildProviderErrorMessage($provider, $response->status(), $response->json(), $response->body()));
        }

        $data = $response->json();

        return $this->parseResponse($provider, $data);
    }

    protected function buildProviderErrorMessage(string $provider, int $status, ?array $data, string $body): string
    {
        $message = data_get($data, 'error.message')
            ?? data_get($data, 'message')
            ?? trim(substr($body, 0, 300));

        $details = $message ? " {$message}" : '';

        if ($provider === 'qwen' && $status === 401) {
            $details .= ' Verifica que la API key pertenezca a US (Virginia) y que el servicio DashScope/Qwen esté activo para esa región.';
        }

        return "Error del proveedor de IA: {$status}.{$details}";
    }

    protected function parseResponse(string $provider, array $data): array
    {
        if ($provider === 'anthropic') {
            $content = '';
            if (isset($data['content']) && is_array($data['content'])) {
                foreach ($data['content'] as $block) {
                    if ($block['type'] === 'text') {
                        $content .= $block['text'];
                    }
                }
            }

            return [
                'content' => $content,
                'tokens_input' => $data['usage']['input_tokens'] ?? 0,
                'tokens_output' => $data['usage']['output_tokens'] ?? 0,
                'model' => $data['model'] ?? null,
                'raw' => $data,
            ];
        }

        $choice = $data['choices'][0] ?? null;
        $message = $choice['message'] ?? [];

        return [
            'content' => $message['content'] ?? '',
            'tokens_input' => $data['usage']['prompt_tokens'] ?? 0,
            'tokens_output' => $data['usage']['completion_tokens'] ?? 0,
            'model' => $data['model'] ?? null,
            'raw' => $data,
        ];
    }

    protected function convertToAnthropicMessages(array $messages): array
    {
        $anthropicMessages = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                continue;
            }

            $anthropicMessages[] = [
                'role' => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content'],
            ];
        }

        return $anthropicMessages;
    }

    protected function logUsage(
        User $user,
        ?User $patient,
        string $actionType,
        string $provider,
        string $model,
        array $response,
        array $originalMessages
    ): AiUsageLog {
        $promptSummary = '';
        if (! empty($originalMessages)) {
            $lastUserMessage = collect($originalMessages)->last(fn ($msg) => $msg['role'] === 'user');
            $promptSummary = $lastUserMessage ? substr($lastUserMessage['content'], 0, 200) : '';
        }

        return AiUsageLog::create([
            'user_id' => $user->id,
            'patient_id' => $patient?->id,
            'action_type' => $actionType,
            'provider' => $provider,
            'model_used' => $model,
            'tokens_input' => $response['tokens_input'] ?? 0,
            'tokens_output' => $response['tokens_output'] ?? 0,
            'cost_estimate' => $this->estimateCost($provider, $model, $response['tokens_input'] ?? 0, $response['tokens_output'] ?? 0),
            'prompt_summary' => $promptSummary,
            'metadata' => [
                'response_length' => strlen($response['content'] ?? ''),
            ],
        ]);
    }

    protected function logAudit(User $user, string $actionType, string $provider, string $model, ?User $patient): void
    {
        AuditAiLog::create([
            'user_id' => $user->id,
            'action' => "ai_{$actionType}",
            'section' => 'ia',
            'model_type' => User::class,
            'model_id' => $patient?->id,
            'payload' => [
                'provider' => $provider,
                'model' => $model,
                'action_type' => $actionType,
                'patient_id' => $patient?->id,
            ],
        ]);
    }

    protected function estimateCost(string $provider, string $model, int $inputTokens, int $outputTokens): float
    {
        $rates = [
            'openai' => [
                'gpt-4o' => ['input' => 2.50, 'output' => 10.00],
                'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60],
                'gpt-4' => ['input' => 30.00, 'output' => 60.00],
                'gpt-3.5-turbo' => ['input' => 0.50, 'output' => 1.50],
            ],
            'deepseek' => [
                'deepseek-chat' => ['input' => 0.14, 'output' => 0.28],
                'deepseek-reasoner' => ['input' => 0.55, 'output' => 2.19],
            ],
            'qwen' => [
                'qwen-plus' => ['input' => 0.40, 'output' => 1.20],
                'qwen-turbo' => ['input' => 0.05, 'output' => 0.20],
                'qwen-max' => ['input' => 1.60, 'output' => 6.40],
            ],
            'anthropic' => [
                'claude-3-5-sonnet-latest' => ['input' => 3.00, 'output' => 15.00],
                'claude-3-5-haiku-latest' => ['input' => 0.80, 'output' => 4.00],
            ],
        ];

        $providerRates = $rates[$provider] ?? [];
        $modelRates = $providerRates[$model] ?? ['input' => 1.00, 'output' => 2.00];

        $inputCost = ($inputTokens / 1_000_000) * $modelRates['input'];
        $outputCost = ($outputTokens / 1_000_000) * $modelRates['output'];

        return round($inputCost + $outputCost, 6);
    }

    protected function buildExpedientePrompt(array $expedienteData): string
    {
        $prompt = "Resumir el siguiente expediente clínico del paciente:\n\n";

        foreach ($expedienteData as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));
            if (is_array($value)) {
                $prompt .= "{$label}:\n";
                foreach ($value as $i => $item) {
                    $prompt .= "  {$i}. ".(is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : $item)."\n";
                }
            } else {
                $prompt .= "{$label}: {$value}\n";
            }
        }

        $prompt .= "\nGenera un resumen conciso con los hallazgos más relevantes.";

        return $prompt;
    }

    protected function buildNotesPrompt(array $consultaData): string
    {
        $prompt = "Generar nota clínica basada en los siguientes datos:\n\n";

        foreach ($consultaData as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));
            $prompt .= "{$label}: ".(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value)."\n";
        }

        return $prompt;
    }
}
