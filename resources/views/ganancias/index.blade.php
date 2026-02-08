<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-chart-line mr-2 text-green-500"></i> Reporte de Ganancias
                        </h1>
                    </div>

                    <!-- Filtros -->
                    <form method="GET" action="{{ route('ganancias.index') }}" class="bg-gray-50 p-4 rounded-lg shadow-inner mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            @if(Auth::user()->hasRole('root'))
                                <div>
                                    <label for="doctor_id" class="block text-sm font-medium text-gray-700">Filtrar por Doctor</label>
                                    <select name="doctor_id" id="doctor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
                                <label for="date_start" class="block text-sm font-medium text-gray-700">Fecha Inicio</label>
                                <input type="date" name="date_start" id="date_start" value="{{ request('date_start') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                            
                            <div>
                                <label for="date_end" class="block text-sm font-medium text-gray-700">Fecha Fin</label>
                                <input type="date" name="date_end" id="date_end" value="{{ request('date_end') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#0061F5] hover:bg-[#0051CC] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5]">
                                    <i class="fas fa-filter mr-2"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Resumen Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-green-50 rounded-xl p-6 border border-green-100 shadow-sm">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-600">
                                    <i class="fas fa-money-bill-wave text-2xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-green-600">Total Ganancias</p>
                                    <p class="text-2xl font-bold text-green-800">${{ number_format($totalGanancias, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->hasRole('root'))
                            <div class="bg-blue-50 rounded-xl p-6 border border-blue-100 shadow-sm">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                        <i class="fas fa-shopping-cart text-2xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-blue-600">Ventas Totales Generadas</p>
                                        <p class="text-2xl font-bold text-blue-800">${{ number_format($totalVentas, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Gráfico / Resumen Diario (Simple) -->
                    @if($gananciasPorDia->count() > 0)
                    <div class="mb-8 bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-800">Desglose por Día</h3>
                        </div>
                        <div class="p-6">
                            <div class="flex items-end space-x-2 h-40 overflow-x-auto pb-2">
                                @foreach($gananciasPorDia as $dia)
                                    <div class="flex flex-col items-center group relative">
                                        <div class="text-xs text-gray-500 mb-1 opacity-0 group-hover:opacity-100 transition-opacity absolute -top-6 w-max bg-gray-800 text-white px-2 py-1 rounded">
                                            ${{ number_format($dia->total, 2) }}
                                        </div>
                                        <div class="w-12 bg-green-200 hover:bg-green-300 transition-colors rounded-t-md" 
                                             style="height: {{ ($dia->total / ($gananciasPorDia->max('total') ?: 1)) * 100 }}%;"></div>
                                        <div class="mt-2 text-xs text-gray-600 rotate-45 origin-left transform translate-y-2 whitespace-nowrap">
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
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item / Servicio</th>
                                    @if(Auth::user()->hasRole('root'))
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                    @endif
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Venta</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ganancia (%)</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Ganado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($ganancias as $ganancia)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($ganancia->fecha)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            {{ $ganancia->concepto }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ optional($ganancia->catalogo)->nombre ?? optional($ganancia->paquete)->nombre ?? 'N/A' }}
                                        </td>
                                        @if(Auth::user()->hasRole('root'))
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
                                            @if(Auth::user()->hasRole('root'))
                                                <!-- Root ve SU ganancia (Total - Ganancia Doctor) -->
                                                + ${{ number_format($ganancia->monto_total - $ganancia->monto_ganancia_doctor, 2) }}
                                            @else
                                                <!-- Doctor ve SU ganancia -->
                                                + ${{ number_format($ganancia->monto_ganancia_doctor, 2) }}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()->hasRole('root') ? 7 : 6 }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
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
