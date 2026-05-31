<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Inteligencia Artificial</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4">
                                <i class="fas fa-cog mr-2"></i>
                                Configuración de IA
                            </h2>

                            <form action="{{ route('ai.config.update') }}" method="POST" x-data="{ provider: '{{ $config->provider }}' }">
                                @csrf
                                @method('PUT')

                                <div class="space-y-6">
                                    <div>
                                        <label for="enabled" class="flex items-center">
                                            <input type="hidden" name="enabled" value="0">
                                            <input type="checkbox" name="enabled" id="enabled" value="1" class="rounded border-gray-300 text-[#0061F5] focus:ring-[#0061F5]" {{ $config->enabled ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm font-bold text-gray-700">Habilitar IA</span>
                                        </label>
                                    </div>

                                    <div>
                                        <label for="provider" class="block text-sm font-bold text-gray-700">Proveedor</label>
                                        <select name="provider" id="provider" x-model="provider" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                            @foreach($providers as $p)
                                                <option value="{{ $p }}" {{ $config->provider === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="api_key" class="block text-sm font-bold text-gray-700">API Key</label>
                                        <input type="password" name="api_key" id="api_key" value="{{ old('api_key', $config->api_key) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" placeholder="sk-...">
                                        <p class="mt-1 text-xs text-gray-500">Se almacena cifrada en la base de datos.</p>
                                    </div>

                                    <hr>

                                    <h3 class="text-md font-bold text-gray-900">Modelos por acción</h3>

                                    @foreach($actionLabels as $action => $label)
                                        <div>
                                            <label for="model_for_{{ $action }}" class="block text-sm font-bold text-gray-700">{{ $label }}</label>
                                            <input type="text" name="model_for_{{ $action }}" id="model_for_{{ $action }}" value="{{ old('model_for_'.$action, $config->{'model_for_'.$action}) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" placeholder="Ej: gpt-4o-mini">
                                        </div>
                                    @endforeach

                                    <div class="flex gap-3">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white rounded-md hover:bg-blue-700">
                                            <i class="fas fa-save mr-2"></i>
                                            Guardar
                                        </button>
                                        <button type="button" id="test-connection" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                            <i class="fas fa-plug mr-2"></i>
                                            Probar conexión
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <div id="test-result" class="mt-4 hidden"></div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Uso del mes
                            </h2>

                            <div class="space-y-4">
                                <div>
                                    <div class="text-sm text-gray-500">Solicitudes</div>
                                    <div class="text-2xl font-bold">{{ $stats['total_requests'] }} / {{ $aiLimit > 0 ? $aiLimit : '∞' }}</div>
                                    @if($aiLimit > 0)
                                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                            <div class="bg-[#0061F5] h-2 rounded-full" style="width: {{ min(100, ($aiUsage / $aiLimit) * 100) }}%"></div>
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <div class="text-sm text-gray-500">Tokens usados</div>
                                    <div class="text-2xl font-bold">{{ number_format($stats['total_tokens']) }}</div>
                                </div>

                                <div>
                                    <div class="text-sm text-gray-500">Costo estimado</div>
                                    <div class="text-2xl font-bold">${{ $stats['total_cost'] }}</div>
                                </div>

                                @if($stats['by_action']->isNotEmpty())
                                    <hr>
                                    <h4 class="text-sm font-bold text-gray-700">Por tipo</h4>
                                    <div class="space-y-2">
                                        @foreach($stats['by_action'] as $action => $data)
                                            <div class="flex justify-between text-sm">
                                                <span>{{ $actionLabels[$action] ?? $action }}</span>
                                                <span class="font-bold">{{ $data['count'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-4">
                                <i class="fas fa-history mr-2"></i>
                                Recientes
                            </h2>

                            <div class="space-y-3">
                                @forelse($recentLogs as $log)
                                    <div class="text-sm border-b border-gray-100 pb-2">
                                        <div class="flex justify-between">
                                            <span class="font-bold">{{ $actionLabels[$log->action_type] ?? $log->action_type }}</span>
                                            <span class="text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="text-gray-500">
                                            {{ $log->model_used }} · {{ $log->totalTokens() }} tokens
                                        </div>
                                        @if($log->user)
                                            <div class="text-gray-400 text-xs">
                                                Usuario: {{ $log->user->nombre_completo ?? $log->user->email }}
                                            </div>
                                        @endif
                                        @if($log->patient)
                                            <div class="text-gray-400 text-xs">
                                                Paciente: {{ $log->patient->nombre_completo }}
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">Sin uso registrado.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('test-connection').addEventListener('click', async function() {
            const btn = this;
            const result = document.getElementById('test-result');
            const apiKey = document.getElementById('api_key').value;
            const provider = document.getElementById('provider').value;
            const model = document.getElementById('model_for_summary').value || 'gpt-4o-mini';

            if (!apiKey) {
                result.classList.remove('hidden');
                result.innerHTML = '<div class="p-3 bg-red-100 border border-red-400 text-red-700 rounded">Ingresa un API key primero.</div>';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Probando...';

            try {
                const res = await fetch('{{ route('ai.config.test') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ provider, api_key: apiKey, model }),
                });

                const data = await res.json();
                result.classList.remove('hidden');

                if (data.success) {
                    result.innerHTML = `<div class="p-3 bg-green-100 border border-green-400 text-green-700 rounded">${data.message} - ${data.content}</div>`;
                } else {
                    result.innerHTML = `<div class="p-3 bg-red-100 border border-red-400 text-red-700 rounded">Error: ${data.message}</div>`;
                }
            } catch (e) {
                result.classList.remove('hidden');
                result.innerHTML = `<div class="p-3 bg-red-100 border border-red-400 text-red-700 rounded">Error de red: ${e.message}</div>`;
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-plug mr-2"></i> Probar conexión';
            }
        });
    </script>
    @endpush
</x-admin-layout>
