<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-[#1E293B] hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Ganancias</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-[#1E293B]">
                            <i class="fas fa-chart-line mr-2 text-[#27ADFA]"></i> Reporte de Ganancias
                        </h1>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('ganancias.export_pdf', request()->query()) }}"
                               class="inline-flex items-center px-4 py-2 bg-white text-[#0061F5] text-sm font-bold rounded-md border border-[#0061F5] hover:bg-blue-50 transition-colors shadow-sm">
                                <i class="fas fa-file-pdf mr-2"></i>
                                Exportar PDF
                            </a>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <style>[x-cloak] { display: none !important; }</style>
                    <form method="GET" action="{{ route('ganancias.index') }}" id="filtros-form" class="bg-[#F8FAFC] p-4 rounded-lg shadow-inner mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end mb-4" x-data="{ periodo: '{{ request('periodo', $periodo) }}' }">
                            <div>
                                <label for="periodo" class="block text-sm font-medium text-[#1E293B]">Periodo</label>
                                <select name="periodo" id="periodo" x-model="periodo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                    <option value="mes_anterior">Mes anterior</option>
                                    <option value="mes_actual">Mes actual</option>
                                    <option value="rango">Rango de fechas</option>
                                </select>
                            </div>

                            <div x-show="periodo === 'rango'" x-cloak>
                                <label for="date_start" class="block text-sm font-medium text-[#1E293B]">Fecha Inicio</label>
                                <input type="date" name="date_start" id="date_start" value="{{ request('date_start', $dateStart) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                            </div>

                            <div x-show="periodo === 'rango'" x-cloak>
                                <label for="date_end" class="block text-sm font-medium text-[#1E293B]">Fecha Fin</label>
                                <input type="date" name="date_end" id="date_end" value="{{ request('date_end', $dateEnd) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                            </div>

                            @if(Auth::user()->hasRole('root'))
                            <div>
                                <label for="doctor_id" class="block text-sm font-medium text-[#1E293B]">Doctor</label>
                                <select name="doctor_id" id="doctor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                    <option value="">Todos los doctores</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }} ({{ $doctor->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div>
                                <label for="tipo_ingreso" class="block text-sm font-medium text-[#1E293B]">Tipo de Ingreso</label>
                                <select name="tipo_ingreso" id="tipo_ingreso" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                    <option value="">Todos</option>
                                    @foreach($tiposIngreso as $value => $label)
                                        <option value="{{ $value }}" {{ request('tipo_ingreso') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#0061F5] hover:bg-[#0051CC] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5]">
                                    <i class="fas fa-filter mr-2"></i> Aplicar
                                </button>
                            </div>
                        </div>

                        <!-- Fila 2: Origen/Servicio -->
                        <div>
                            <label class="block text-sm font-medium text-[#1E293B] mb-2">Origen / Servicio</label>
                            <div class="flex flex-wrap items-center gap-3" x-data="{ todosChecked: {{ request('origen') ? 'false' : 'true' }} }">
                                <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md border border-gray-200 bg-white text-sm text-[#1E293B] cursor-pointer hover:border-[#0061F5] transition-colors select-none" :class="{ '!border-[#0061F5] !bg-[#EFF6FF]': todosChecked }">
                                    <input type="checkbox"
                                        value="1"
                                        class="rounded border-gray-300 text-[#0061F5] focus:ring-[#0061F5]"
                                        x-model="todosChecked"
                                        @change="if(todosChecked) { $el.closest('.flex-wrap').querySelectorAll('input[name^=\\'origen\\]').forEach(c => { c.checked = false; }); }">
                                    <span>Todos</span>
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
                                    <p class="text-sm font-medium text-[#0061F5]">Total Ganancias</p>
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
                                        <p class="text-sm font-medium text-[#0061F5]">Ventas Totales Generadas</p>
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
                                        <p class="text-sm font-medium text-[#0061F5]">Ventas Totales Generadas</p>
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
                            <h3 class="text-lg font-medium text-[#1E293B]">Desglose por Día</h3>
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">FECHA</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">CONCEPTO</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">SERVICIO</th>
                                    @if(!Auth::user()->hasRole('doctor'))
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">TIPO</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">DOCTOR</th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">MONTO VENTA</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">GANANCIA (%)</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">GANANCIA ($)</th>
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
                                            {{ optional($ganancia->catalogo)->nombre ?? optional($ganancia->paquete)->nombre ?? 'N/A' }}
                                        </td>
                                        @if(!Auth::user()->hasRole('doctor'))
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($ganancia->tipo_ingreso === 'compra')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        <i class="fas fa-shopping-cart mr-1"></i> Compra
                                                    </span>
                                                @elseif($ganancia->tipo_ingreso === 'renovacion')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-sync-alt mr-1"></i> Renovación
                                                    </span>
                                                @else
                                                    <span class="text-sm text-gray-500">—</span>
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
                                            No hay registros de ganancias en este periodo.
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
