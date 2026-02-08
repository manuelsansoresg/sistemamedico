<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Clínicas</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Listado de Clínicas</h2>
                        <a href="{{ route('clinicas.create') }}" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm flex items-center">
                            <i class="fas fa-plus mr-2"></i> NUEVA CLÍNICA
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">NOMBRE</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">DIRECCIÓN</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">TELÉFONO</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">LOGOTIPO</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">ESTADO</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">CREADO POR</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-[#0061F5] uppercase tracking-wider">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($clinicas as $clinica)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-cyan-500">{{ $clinica->nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-cyan-500">{{ $clinica->direccion }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-cyan-500">{{ $clinica->telefono }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($clinica->logotipo)
                                                <img src="{{ asset($clinica->logotipo) }}" alt="Logotipo" class="h-10 w-10 rounded-full object-cover">
                                            @else
                                                <span class="text-sm text-gray-400">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-cyan-500">
                                            {{ $clinica->activo ? 'Activo' : 'Inactivo' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-cyan-500">
                                            {{ $clinica->creator->name ?? 'Desconocido' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <a href="{{ route('clinicas.edit', $clinica) }}" class="inline-flex items-center justify-center w-9 h-9 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('clinicas.destroy', $clinica) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta clínica?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center w-9 h-9 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            No hay clínicas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $clinicas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
