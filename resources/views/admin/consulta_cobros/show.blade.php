<x-admin-layout>
    <div class="py-10" x-data="cobroViewer()" x-init="startPolling()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>{{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('citas.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">{{ __('citas.title') }}</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('cobros.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-[#0061F5]">
                <div class="p-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ $cita->paciente->name }} {{ $cita->paciente->apellido_paterno }} {{ $cita->paciente->apellido_materno }}</h1>
                        <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-600">
                            <span><i class="fas fa-user-md mr-2 text-[#0061F5]"></i>{{ $cita->doctor->name }} {{ $cita->doctor->apellido_paterno }}</span>
                            <span><i class="fas fa-calendar mr-2 text-[#0061F5]"></i>{{ $cita->fecha->format('d/m/Y') }}</span>
                            <span><i class="fas fa-clock mr-2 text-[#0061F5]"></i>{{ $cita->hora_inicio->format('H:i') }} - {{ $cita->hora_fin?->format('H:i') }}</span>
                            <span><i class="fas fa-phone mr-2 text-[#0061F5]"></i>{{ $cita->paciente->telefono ?? __('cobros.ui.no_phone') }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">{{ __('cobros.fields.total') }}</div>
                        <div class="text-3xl font-extrabold text-[#1E293B]">$<span x-text="status.total">{{ number_format((float) ($cobro?->total ?? 0), 2) }}</span></div>
                        <div class="mt-2 text-xs text-gray-400" x-show="status.updated_at">{{ __('cobros.ui.updated_at') }} <span x-text="status.updated_at"></span></div>
                    </div>
                </div>
            </div>

            @if(!$cobro)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md shadow-sm">
                    <p class="text-sm text-yellow-800 font-medium">{{ __('cobros.messages.no_charge_yet') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-bold text-gray-800">{{ __('cobros.sections.breakdown') }}</h2>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800" x-text="status.estado_instrucciones">{{ trans_enum('cobros.statuses.'.$cobro->estado_instrucciones, $cobro->estado_instrucciones) }}</span>
                            </div>

                            <div class="mb-6 bg-[#F8FAFC] border border-gray-100 rounded-lg p-4">
                                <h3 class="text-sm font-bold text-gray-700 mb-2">{{ __('cobros.fields.instructions') }}</h3>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $cobro->instrucciones_cobro ?: __('cobros.messages.without_instructions') }}</p>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('cobros.columns.type') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('cobros.columns.description') }}</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">{{ __('cobros.fields.catalog_price') }}</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">{{ __('cobros.fields.charged_price') }}</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">{{ __('cobros.fields.quantity') }}</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">{{ __('cobros.fields.subtotal') }}</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">{{ __('common.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($cobro->items as $item)
                                            <tr>
                                                <form id="item-update-{{ $item->id }}" method="POST" action="{{ route('consulta-cobro-items.update', $item) }}">
                                                    @csrf
                                                    @method('PUT')
                                                </form>
                                                <form id="item-delete-{{ $item->id }}" method="POST" action="{{ route('consulta-cobro-items.destroy', $item) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                <td class="px-4 py-3 text-sm text-gray-500">{{ trans_enum('cobros.item_types.'.$item->tipo, ucfirst($item->tipo)) }}</td>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-700">
                                                    {{ $item->nombre_snapshot }}
                                                    @if($item->duracion_minutos_snapshot > 0)
                                                        <div class="text-xs text-gray-400">{{ $item->duracion_minutos_snapshot }} min</div>
                                                    @endif
                                                    @if($item->precio_modificado)
                                                        <div class="text-xs text-orange-600 font-semibold">{{ __('cobros.ui.modified_price') }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500 text-right">${{ number_format($item->precio_catalogo, 2) }}</td>
                                                <td class="px-4 py-3 text-right">
                                                    <input form="item-update-{{ $item->id }}" type="number" step="0.01" min="0" name="precio_cobrado" value="{{ $item->precio_cobrado }}" class="w-28 rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] text-sm text-right">
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <input form="item-update-{{ $item->id }}" type="number" min="1" name="cantidad" value="{{ $item->cantidad }}" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] text-sm text-right" {{ $item->tipo === 'servicio' ? 'readonly' : '' }}>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700 font-bold text-right">${{ number_format($item->subtotal, 2) }}</td>
                                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                                    <button form="item-update-{{ $item->id }}" type="submit" class="inline-flex items-center justify-center w-9 h-9 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC]" title="{{ __('common.buttons.save') }}"><i class="fas fa-save"></i></button>
                                                    <button form="item-delete-{{ $item->id }}" type="submit" class="inline-flex items-center justify-center w-9 h-9 bg-red-600 text-white rounded-md hover:bg-red-700" onclick="return confirm('{{ __('common.confirm_delete') }}')" title="{{ __('common.buttons.delete') }}"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-lg font-bold text-gray-800 mb-4">{{ __('cobros.sections.add_article') }}</h2>
                                <form method="POST" action="{{ route('consulta-cobros.articulos.store', $cobro) }}" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700">{{ __('cobros.fields.article') }}</label>
                                        <select name="articulo_cobro_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                            <option value="">{{ __('cobros.placeholders.select_article') }}</option>
                                            @foreach($articulos as $articulo)
                                                <option value="{{ $articulo->id }}">{{ $articulo->nombre }} - ${{ number_format($articulo->precio, 2) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700">{{ __('cobros.fields.quantity') }}</label>
                                            <input type="number" name="cantidad" value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700">{{ __('cobros.fields.charged_price') }}</label>
                                            <input type="number" name="precio_cobrado" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-[#0061F5] text-white text-sm font-bold rounded-md hover:bg-[#0051CC]">
                                        <i class="fas fa-plus mr-2"></i>{{ __('cobros.actions.add_article') }}
                                    </button>
                                </form>
                                <a href="{{ route('articulos-cobro.index') }}" class="mt-3 inline-flex text-sm font-semibold text-[#0061F5] hover:text-[#004499]">{{ __('cobros.actions.manage_articles') }}</a>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 space-y-2">
                                <div class="flex justify-between text-sm"><span>{{ __('cobros.fields.services_subtotal') }}</span><strong>${{ number_format($cobro->subtotal_servicios, 2) }}</strong></div>
                                <div class="flex justify-between text-sm"><span>{{ __('cobros.fields.articles_subtotal') }}</span><strong>${{ number_format($cobro->subtotal_articulos, 2) }}</strong></div>
                                <div class="border-t pt-2 flex justify-between text-lg font-extrabold"><span>{{ __('cobros.fields.total') }}</span><span>${{ number_format($cobro->total, 2) }}</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">{{ __('cobros.sections.affected_appointments') }}</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('cobros.columns.patient') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('cobros.columns.contact') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('cobros.columns.original_time') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('cobros.columns.status') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('cobros.fields.notes') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($cobro->afectaciones as $afectacion)
                                        <tr>
                                            <form id="afectacion-{{ $afectacion->id }}" method="POST" action="{{ route('cita-afectaciones.update', $afectacion) }}">
                                                @csrf
                                                @method('PATCH')
                                            </form>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-700">{{ $afectacion->paciente_nombre_snapshot }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                <div>{{ $afectacion->paciente_telefono_snapshot ?? __('cobros.ui.no_phone') }}</div>
                                                <div>{{ $afectacion->paciente_email_snapshot ?? __('cobros.ui.no_email') }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ \Carbon\Carbon::parse($afectacion->hora_inicio_original)->format('H:i') }} - {{ $afectacion->hora_fin_original ? \Carbon\Carbon::parse($afectacion->hora_fin_original)->format('H:i') : __('cobros.ui.dash') }}</td>
                                            <td class="px-4 py-3">
                                                <select form="afectacion-{{ $afectacion->id }}" name="estado" class="rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] text-sm">
                                                    @foreach(['pendiente_aviso', 'avisado', 'reagendada', 'no_localizado'] as $estado)
                                                        <option value="{{ $estado }}" {{ $afectacion->estado === $estado ? 'selected' : '' }}>{{ trans_enum('cobros.affectation_statuses.'.$estado, $estado) }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-4 py-3"><input form="afectacion-{{ $afectacion->id }}" type="text" name="notas" value="{{ $afectacion->notas }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] text-sm"></td>
                                            <td class="px-4 py-3 text-right"><button form="afectacion-{{ $afectacion->id }}" type="submit" class="inline-flex items-center px-3 py-2 bg-[#0061F5] text-white text-xs font-bold rounded-md hover:bg-[#0051CC]"><i class="fas fa-save mr-2"></i>{{ __('common.buttons.save') }}</button></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('cobros.messages.no_affected_appointments') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function cobroViewer() {
            return {
                status: {
                    total: '{{ number_format((float) ($cobro?->total ?? 0), 2) }}',
                    estado_instrucciones: '{{ trans_enum('cobros.statuses.'.($cobro?->estado_instrucciones ?? 'pendiente'), $cobro?->estado_instrucciones ?? 'pendiente') }}',
                    updated_at: '{{ $cobro?->updated_at?->format('d/m/Y H:i:s') }}',
                },
                async fetchStatus() {
                    try {
                        const response = await fetch('{{ route('consulta-cobros.status', $cita) }}', { headers: { 'Accept': 'application/json' } });
                        if (response.ok) {
                            this.status = await response.json();
                        }
                    } catch (error) {
                    }
                },
                startPolling() {
                    this.fetchStatus();
                    setInterval(() => this.fetchStatus(), 5000);
                },
            }
        }
    </script>
</x-admin-layout>
