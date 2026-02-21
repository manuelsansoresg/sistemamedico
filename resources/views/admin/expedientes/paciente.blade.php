<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('expedientes.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">Expedientes</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Historial del Paciente</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-[#0061F5]">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ $paciente->name }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Historial completo de consultas y estudios del paciente.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Clínica</label>
                        <select name="clinica_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                            <option value="">Todas</option>
                            @foreach($clinicas as $clinica)
                                <option value="{{ $clinica->id }}" @selected(request('clinica_id') == $clinica->id)>{{ $clinica->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Consultorio</label>
                        <select name="consultorio_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                            <option value="">Todos</option>
                            @foreach($consultorios as $consultorio)
                                <option value="{{ $consultorio->id }}" @selected(request('consultorio_id') == $consultorio->id)>{{ $consultorio->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                        <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                        <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                    </div>
                    <div class="md:col-span-4 flex justify-end mt-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0051CC] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5]">
                            <i class="fas fa-filter mr-2"></i> Filtrar
                        </button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">Clínica</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">Consultorio</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">Doctor</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">Detalle</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-[#0061F5] uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($expedientes as $expediente)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ optional($expediente->cita->fecha)->format('d/m/Y') }}
                                        <div class="text-xs text-gray-500">
                                            {{ optional($expediente->cita->hora_inicio)->format('H:i') }} - {{ optional($expediente->cita->hora_fin)->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $expediente->cita->clinica->nombre ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $expediente->cita->consultorio->nombre ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $expediente->doctor->name }} {{ $expediente->doctor->apellido_paterno }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="text-xs text-gray-500">
                                            <span class="font-semibold">Motivo:</span>
                                            {{ \Illuminate\Support\Str::limit($expediente->cita->motivo ?? 'Sin motivo registrado', 60) }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <span class="font-semibold">Plantilla:</span>
                                            {{ $expediente->plantilla->nombre ?? 'Sin plantilla' }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <span class="font-semibold">Estudios:</span>
                                            {{ $expediente->estudios->count() }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end items-center space-x-2">
                                            <a href="{{ route('consultas.show', $expediente->id) }}" class="inline-flex items-center justify-center w-9 h-9 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors shadow-sm" title="Ver Consulta">
                                                <i class="fas fa-eye text-sm"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No hay consultas registradas para este paciente con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $expedientes->links() }}
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

