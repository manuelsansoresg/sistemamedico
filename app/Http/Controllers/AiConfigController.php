<?php

namespace App\Http\Controllers;

use App\Models\AiConfig;
use App\Models\AiUsageLog;
use App\Services\AiService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class AiConfigController extends Controller
{
    public function index(AiService $aiService, SubscriptionService $subscriptionService)
    {
        $config = $aiService->ensureConfigExists();
        $stats = $subscriptionService->getGlobalAiUsageStats('month');
        $limits = $subscriptionService->getGlobalAiLimits();
        $aiLimit = $limits['ai_requests'] ?? 0;
        $aiUsage = $subscriptionService->getGlobalAiUsageCount();

        $recentLogs = AiUsageLog::with(['user', 'patient'])
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.ai.config', [
            'config' => $config,
            'stats' => $stats,
            'aiLimit' => $aiLimit,
            'aiUsage' => $aiUsage,
            'recentLogs' => $recentLogs,
            'providers' => array_keys(AiService::PROVIDERS),
            'actionLabels' => AiConfig::actionLabels(),
        ]);
    }

    public function update(Request $request, AiService $aiService)
    {
        $config = $aiService->ensureConfigExists();

        $validated = $request->validate([
            'provider' => 'required|string|in:openai,deepseek,anthropic',
            'api_key' => 'nullable|string',
            'model_for_summary' => 'nullable|string',
            'model_for_assistant' => 'nullable|string',
            'model_for_notes' => 'nullable|string',
            'model_for_diagnosis' => 'nullable|string',
            'model_for_prescription' => 'nullable|string',
            'enabled' => 'nullable|boolean',
        ]);

        if (empty($validated['api_key'])) {
            unset($validated['api_key']);
        }

        $validated['enabled'] = $request->has('enabled');

        $config->update($validated);

        return redirect()->route('ai.config')->with('success', __('ia.messages.config_updated'));
    }

    public function test(Request $request, AiService $aiService)
    {
        $validated = $request->validate([
            'provider' => 'required|string|in:openai,deepseek,anthropic',
            'api_key' => 'required|string',
            'model' => 'required|string',
        ]);

        try {
            $messages = [
                ['role' => 'system', 'content' => 'Responde solo con "OK" para confirmar conexión.'],
                ['role' => 'user', 'content' => 'Test de conexión'],
            ];

            $response = $aiService->callProviderDirect(
                $validated['provider'],
                $validated['api_key'],
                $validated['model'],
                $messages,
                ['max_tokens' => 10]
            );

            return response()->json([
                'success' => true,
                'message' => __('ia.messages.connection_success'),
                'content' => $response['content'] ?? '',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
