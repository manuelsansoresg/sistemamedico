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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Plantillas</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Listado de Plantillas</h2>
                        <a href="{{ route('plantillas.create') }}" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm flex items-center">
                            <i class="fas fa-plus mr-2"></i> NUEVA PLANTILLA
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
                                    @role('root')
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">DOCTOR ASIGNADO</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">CREADO POR</th>
                                    @endrole
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">FECHA CREACIÓN</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-[#0061F5] uppercase tracking-wider">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($plantillas as $plantilla)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $plantilla->nombre }}</td>
                                        @role('root')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $plantilla->user->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $plantilla->creator->name ?? 'N/A' }}</td>
                                        @endrole
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $plantilla->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <a href="{{ route('plantillas.edit', $plantilla) }}" class="inline-flex items-center justify-center w-9 h-9 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('plantillas.destroy', $plantilla) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta plantilla?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center w-9 h-9 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="Eliminar">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->hasRole('root') ? '5' : '3' }}" class="px-6 py-4 text-center text-gray-500">
                                            No hay plantillas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $plantillas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
