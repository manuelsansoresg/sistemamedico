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

            <div class="space-y-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-lg border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Estado</p>
                                <p class="mt-1 text-xl font-bold {{ $config->enabled ? 'text-emerald-600' : 'text-gray-500' }}">{{ $config->enabled ? 'Activa' : 'Desactivada' }}</p>
                            </div>
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-md {{ $config->enabled ? 'bg-emerald-50 text-emerald-600' : 'bg-gray-100 text-gray-500' }}">
                                <i class="fas {{ $config->enabled ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                            </span>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Proveedor</p>
                                <p class="mt-1 truncate text-xl font-bold text-gray-900">{{ ucfirst($config->provider) }}</p>
                            </div>
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-md bg-blue-50 text-[#0061F5]">
                                <i class="fas fa-microchip"></i>
                            </span>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Solicitudes del mes</p>
                                <p class="mt-1 text-xl font-bold text-gray-900">{{ $stats['total_requests'] }} / {{ $aiLimit > 0 ? $aiLimit : '∞' }}</p>
                            </div>
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-md bg-orange-50 text-[#FA7427]">
                                <i class="fas fa-chart-line"></i>
                            </span>
                        </div>
                        @if($aiLimit > 0)
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-[#0061F5]" style="width: {{ min(100, ($aiUsage / $aiLimit) * 100) }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>

                <form action="{{ route('ai.config.update') }}" method="POST" x-data="{ provider: '{{ $config->provider }}' }" class="rounded-lg border border-gray-100 bg-white shadow-sm">
                    @csrf
                    @method('PUT')

                    <div class="border-b border-gray-100 px-6 py-5">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">
                                    <i class="fas fa-cog mr-2 text-[#0061F5]"></i>
                                    Configuración de IA
                                </h2>
                                <p class="mt-1 text-sm text-gray-500">Define proveedor, clave y modelos para cada acción clínica.</p>
                            </div>
                            <label for="enabled" class="inline-flex w-fit items-center gap-3 rounded-md border border-gray-200 bg-[#F8FAFC] px-3 py-2">
                                <input type="hidden" name="enabled" value="0">
                                <input type="checkbox" name="enabled" id="enabled" value="1" class="rounded border-gray-300 text-[#0061F5] focus:ring-[#0061F5]" {{ $config->enabled ? 'checked' : '' }}>
                                <span class="text-sm font-bold text-gray-700">Habilitar IA</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
                        <div class="space-y-5 lg:col-span-1">
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

                            <div id="test-result" class="hidden"></div>
                        </div>

                        <div class="lg:col-span-2">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <h3 class="text-sm font-bold uppercase tracking-wide text-gray-500">Modelos por acción</h3>
                                <span class="text-xs text-gray-400">{{ count($actionLabels) }} acciones</span>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                @foreach($actionLabels as $action => $label)
                                    <div>
                                        <label for="model_for_{{ $action }}" class="block text-sm font-bold text-gray-700">{{ $label }}</label>
                                        <input type="text" name="model_for_{{ $action }}" id="model_for_{{ $action }}" value="{{ old('model_for_'.$action, $config->{'model_for_'.$action}) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" placeholder="Ej: qwen3.6-flash">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-[#F8FAFC] px-6 py-4 sm:flex-row sm:justify-end">
                        <button type="button" id="test-connection" class="inline-flex items-center justify-center rounded-md bg-gray-700 px-4 py-2 text-sm font-bold text-white hover:bg-gray-800">
                            <i class="fas fa-plug mr-2"></i>
                            Probar conexión
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-[#0061F5] px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i>
                            Guardar
                        </button>
                    </div>
                </form>

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                    <div class="rounded-lg border border-gray-100 bg-white p-6 shadow-sm xl:col-span-4">
                        <h2 class="text-lg font-bold text-gray-900">
                            <i class="fas fa-list-ul mr-2 text-[#0061F5]"></i>
                            Uso por tipo
                        </h2>

                        <div class="mt-4 space-y-2">
                            @forelse($stats['by_action'] as $action => $data)
                                <div class="flex items-center justify-between gap-3 rounded-md border border-gray-100 px-3 py-2">
                                    <span class="min-w-0 truncate text-sm text-gray-700">{{ $actionLabels[$action] ?? $action }}</span>
                                    <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-700">{{ $data['count'] }}</span>
                                </div>
                            @empty
                                <div class="rounded-md border border-dashed border-gray-200 p-4 text-sm text-gray-500">Sin uso registrado.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-white shadow-sm xl:col-span-8">
                        <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-6 py-5">
                            <h2 class="text-lg font-bold text-gray-900">
                                <i class="fas fa-history mr-2 text-[#0061F5]"></i>
                                Actividad reciente
                            </h2>
                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600">{{ $recentLogs->count() }}</span>
                        </div>

                        <div class="max-h-[24rem] overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-100">
                                <thead class="sticky top-0 bg-[#F8FAFC]">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Acción</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Modelo</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Paciente</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse($recentLogs as $log)
                                        <tr class="hover:bg-[#F8FAFC]">
                                            <td class="max-w-[12rem] px-6 py-3 text-sm font-bold text-gray-900">
                                                <div class="truncate">{{ $actionLabels[$log->action_type] ?? $log->action_type }}</div>
                                                @if($log->user)
                                                    <div class="truncate text-xs font-normal text-gray-400">{{ $log->user->nombre_completo ?? $log->user->email }}</div>
                                                @endif
                                            </td>
                                            <td class="max-w-[10rem] px-6 py-3 text-sm text-gray-600">
                                                <div class="truncate">{{ $log->model_used }}</div>
                                            </td>
                                            <td class="max-w-[12rem] px-6 py-3 text-sm text-gray-500">
                                                <div class="truncate">{{ $log->patient?->nombre_completo ?? 'Sin paciente' }}</div>
                                            </td>
                                            <td class="px-6 py-3 text-right text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Sin uso registrado.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
            const model = document.getElementById('model_for_assistant').value || document.getElementById('model_for_summary').value || 'gpt-4o-mini';

            if (!apiKey) {
                result.classList.remove('hidden');
                result.innerHTML = '<div class="p-3 bg-red-100 border border-red-400 text-red-700 rounded">Ingresa un API key primero.</div>';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Probando...';

            try {
                const res = await fetch('{{ route('ai.config.test', [], false) }}', {
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
