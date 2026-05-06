<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-[#1E293B] hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('earnings.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-[#1E293B]">
                            <i class="fas fa-chart-line mr-2 text-[#27ADFA]"></i> {{ __('earnings.title') }}
                        </h1>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('ganancias.export_pdf', request()->query()) }}"
                               class="inline-flex items-center px-4 py-2 bg-white text-[#0061F5] text-sm font-bold rounded-md border border-[#0061F5] hover:bg-blue-50 transition-colors shadow-sm">
                                <i class="fas fa-file-pdf mr-2"></i>
                                {{ __('common.buttons.export_pdf') }}
                            </a>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <style>[x-cloak] { display: none !important; }</style>
                    <form method="GET" action="{{ route('ganancias.index') }}" id="filtros-form" class="bg-[#F8FAFC] p-4 rounded-lg shadow-inner mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end mb-4" x-data="{ periodo: '{{ request('periodo', $periodo) }}' }">
                            <div>
                                <label for="periodo" class="block text-sm font-medium text-[#1E293B]">{{ __('earnings.filters.month') }}</label>
                                <select name="periodo" id="periodo" x-model="periodo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                    <option value="mes_anterior">{{ __('earnings.ui.period_previous_month') }}</option>
                                    <option value="mes_actual">{{ __('earnings.ui.period_current_month') }}</option>
                                    <option value="rango">{{ __('earnings.ui.period_date_range') }}</option>
                                </select>
                            </div>

                            <div x-show="periodo === 'rango'" x-cloak>
                                <label for="date_start" class="block text-sm font-medium text-[#1E293B]">{{ __('earnings.filters.date_start') }}</label>
                                <input type="date" name="date_start" id="date_start" value="{{ request('date_start', $dateStart) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                            </div>

                            <div x-show="periodo === 'rango'" x-cloak>
                                <label for="date_end" class="block text-sm font-medium text-[#1E293B]">{{ __('earnings.filters.date_end') }}</label>
                                <input type="date" name="date_end" id="date_end" value="{{ request('date_end', $dateEnd) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                            </div>

                            @if(Auth::user()->hasRole('root'))
                            <div>
                                <label for="doctor_id" class="block text-sm font-medium text-[#1E293B]">{{ __('earnings.ui.doctor_label') }}</label>
                                <select name="doctor_id" id="doctor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                    <option value="">{{ __('earnings.filters.all_doctors') }}</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }} ({{ $doctor->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div>
                                <label for="tipo_ingreso" class="block text-sm font-medium text-[#1E293B]">{{ __('earnings.filters.income_type') }}</label>
                                <select name="tipo_ingreso" id="tipo_ingreso" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                    <option value="">{{ __('earnings.filters.all_income') }}</option>
                                    @foreach($tiposIngreso as $value => $label)
                                        <option value="{{ $value }}" {{ request('tipo_ingreso') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#0061F5] hover:bg-[#0051CC] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5]">
                                    <i class="fas fa-filter mr-2"></i> {{ __('common.buttons.apply') }}
                                </button>
                            </div>
                        </div>

                        <!-- Fila 2: Origen/Servicio -->
                        <div>
                            <label class="block text-sm font-medium text-[#1E293B] mb-2">{{ __('earnings.ui.origin_service') }}</label>
                            <div class="flex flex-wrap items-center gap-3" x-data="{ todosChecked: {{ request('origen') ? 'false' : 'true' }} }">
                                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm text-[#1E293B] cursor-pointer hover:border-[#0061F5] transition-colors select-none" :class="{ '!border-[#0061F5] !bg-[#EFF6FF]': todosChecked }">
                                    <input type="checkbox"
                                        value="1"
                                        class="rounded border-gray-300 text-[#0061F5] focus:ring-[#0061F5]"
                                        x-model="todosChecked"
                                        @change="if(todosChecked) { $el.closest('.flex-wrap').querySelectorAll('input[name^=\\'origen\\]').forEach(c => { c.checked = false; }); }">
                                    <span>{{ __('earnings.ui.all') }}</span>
                                </label>

                                @foreach($servicios as $servicio)
                                    @php
                                        $checked = in_array($servicio, (array) request('origen', []));
                                    @endphp
                                    <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm text-[#1E293B] cursor-pointer hover:border-[#0061F5] transition-colors select-none"
                                        :class="{ '!border-[#0061F5] !bg-[#EFF6FF]': $el.querySelector('input').checked }">
                                        <input type="checkbox"
                                            name="origen[]"
                                            value="{{ $servicio }}"
                                            class="rounded border-gray-300 text-[#0061F5] focus:ring-[#0061F5]"
                                            {{ $checked ? 'checked' : '' }}
                                            @change="if($event.target.checked) { todosChecked = false; } else { if($el.closest('.flex-wrap').querySelectorAll('input[name^=\\'origen\\]:checked').length === 0) todosChecked = true; }">
                                        <span>{{ $servicio }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </form>

                    <!-- Resumen Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="rounded-xl p-6 border shadow-sm bg-[#27ADFA]/10 border-[#27ADFA]/30">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-[#27ADFA]/20 text-[#0061F5]">
                                    <i class="fas fa-money-bill-wave text-2xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-[#0061F5]">{{ __('earnings.cards.total_profit') }}</p>
                                    <p class="text-2xl font-bold text-[#1E293B]">${{ number_format($totalGanancias, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->hasRole('root'))
                            <div class="rounded-xl p-6 border shadow-sm bg-[#0061F5]/5 border-[#0061F5]/30">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-full bg-[#0061F5]/20 text-[#0061F5]">
                                        <i class="fas fa-shopping-cart text-2xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-[#0061F5]">{{ __('earnings.cards.total_sales') }}</p>
                                        <p class="text-2xl font-bold text-[#1E293B]">${{ number_format($totalVentas, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        @elseif(!Auth::user()->hasRole('doctor'))
                            <div class="rounded-xl p-6 border shadow-sm bg-[#0061F5]/5 border-[#0061F5]/30">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-full bg-[#0061F5]/20 text-[#0061F5]">
                                        <i class="fas fa-shopping-cart text-2xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-[#0061F5]">{{ __('earnings.cards.total_sales') }}</p>
                                        <p class="text-2xl font-bold text-[#1E293B]">${{ number_format($totalVentas, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Gráfico / Resumen Diario -->
                    @if($gananciasPorDia->count() > 0)
                    <div class="mb-8 bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-[#F8FAFC]">
                            <h3 class="text-lg font-medium text-[#1E293B]">{{ __('earnings.ui.breakdown_by_day') }}</h3>
                        </div>
                        <div class="p-6">
                            @php
                                $maxTotal = $gananciasPorDia->max('total') ?: 1;
                            @endphp
                            <div class="flex items-end space-x-2 h-40 overflow-x-auto pb-2">
                                @foreach($gananciasPorDia as $dia)
                                    @php
                                        $percent = ($dia->total / $maxTotal) * 100;
                                        if ($percent >= 75) {
                                            $heightClass = 'h-40';
                                        } elseif ($percent >= 50) {
                                            $heightClass = 'h-32';
                                        } elseif ($percent >= 25) {
                                            $heightClass = 'h-24';
                                        } elseif ($percent > 0) {
                                            $heightClass = 'h-16';
                                        } else {
                                            $heightClass = 'h-0';
                                        }
                                    @endphp
                                    <div class="flex flex-col items-center group relative">
                                        <div class="text-xs text-[#F8FAFC] mb-1 opacity-0 group-hover:opacity-100 transition-opacity absolute -top-6 w-max bg-[#1E293B] text-white px-2 py-1 rounded">
                                            ${{ number_format($dia->total, 2) }}
                                        </div>
                                        <div class="w-8 md:w-10 bg-[#27ADFA] hover:bg-[#0061F5] transition-colors rounded-t-md {{ $heightClass }}"></div>
                                        <div class="mt-2 text-xs text-[#1E293B] whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($dia->dia)->format('d/m') }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Tabla Detallada -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('earnings.columns.date') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('earnings.columns.concept') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('earnings.columns.service') }}</th>
                                    @if(!Auth::user()->hasRole('doctor'))
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('earnings.columns.type') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('earnings.columns.doctor') }}</th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('earnings.columns.sale_amount') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('earnings.columns.profit_percent') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('earnings.columns.profit_amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($ganancias as $ganancia)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($ganancia->fecha)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                            {{ $ganancia->concepto }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ optional($ganancia->catalogo)->nombre ?? optional($ganancia->paquete)->nombre ?? __('earnings.ui.not_available') }}
                                        </td>
                                        @if(!Auth::user()->hasRole('doctor'))
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($ganancia->tipo_ingreso === 'compra')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <i class="fas fa-shopping-cart mr-1"></i> {{ __('earnings.ui.type_purchase') }}
                                                    </span>
                                                @elseif($ganancia->tipo_ingreso === 'renovacion')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-sync-alt mr-1"></i> {{ __('earnings.ui.type_renewal') }}
                                                    </span>
                                                @else
                                                    <span class="text-sm text-gray-500">{{ __('earnings.ui.dash') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $ganancia->user->name }}
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            ${{ number_format($ganancia->monto_total, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            {{ $ganancia->porcentaje_aplicado }}%
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-bold text-right">
                                            @if(!Auth::user()->hasRole('doctor'))
                                                + ${{ number_format($ganancia->monto_total - $ganancia->monto_ganancia_doctor, 2) }}
                                            @else
                                                + ${{ number_format($ganancia->monto_ganancia_doctor, 2) }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td colspan="{{ Auth::user()->hasRole('doctor') ? 6 : 8 }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            {{ __('earnings.ui.no_records_period') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $ganancias->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
