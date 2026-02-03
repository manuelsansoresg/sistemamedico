<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Citas</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Listado de Citas</h2>
                        <a href="{{ route('citas.create') }}" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-md hover:bg-blue-700 transition-colors shadow-sm flex items-center">
                            <i class="fas fa-plus mr-2"></i> NUEVA CITA
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-600 uppercase tracking-wider">FECHA/HORA</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-600 uppercase tracking-wider">DOCTOR</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-600 uppercase tracking-wider">PACIENTE</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-600 uppercase tracking-wider">CONSULTORIO</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-600 uppercase tracking-wider">ESTADO</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-blue-600 uppercase tracking-wider">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($citas as $cita)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-cyan-500">
                                            {{ $cita->fecha->format('d/m/Y') }} <br>
                                            {{ $cita->hora_inicio->format('g:i A') }} - {{ $cita->hora_fin->format('g:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-cyan-500">
                                            {{ $cita->doctor->name }} {{ $cita->doctor->apellido_paterno }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-cyan-500">
                                            {{ $cita->paciente->name }} {{ $cita->paciente->apellido_paterno }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-cyan-500">
                                            {{ $cita->consultorio->nombre ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($cita->estado === 'pendiente') bg-yellow-100 text-yellow-800 
                                                @elseif($cita->estado === 'confirmada') bg-green-100 text-green-800 
                                                @elseif($cita->estado === 'cancelada') bg-red-100 text-red-800 
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ ucfirst($cita->estado) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <a href="{{ route('consultas.create', ['cita_id' => $cita->id]) }}" 
                                                   onclick="return confirm('¿Deseas iniciar la consulta? Se redirigirá a la página de registro de consulta.')"
                                                   class="inline-flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors shadow-sm" 
                                                   title="Iniciar Consulta">
                                                    <i class="fas fa-stethoscope text-lg"></i>
                                                </a>
                                                <a href="{{ route('citas.edit', $cita) }}" class="inline-flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors shadow-sm" title="Editar">
                                                    <i class="fas fa-edit text-lg"></i>
                                                </a>
                                                <form action="{{ route('citas.destroy', $cita) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta cita?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center w-10 h-10 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="Eliminar">
                                                        <i class="fas fa-trash text-lg"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay citas registradas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $citas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
