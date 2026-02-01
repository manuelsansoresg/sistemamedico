<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pendientes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Listado de Pendientes</h2>
                        <a href="{{ route('pendientes.create') }}" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-md hover:bg-blue-700 transition-colors shadow-sm flex items-center">
                            <i class="fas fa-plus mr-2"></i> NUEVO PENDIENTE
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
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-600 uppercase tracking-wider">FECHA</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-600 uppercase tracking-wider">HORA</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-600 uppercase tracking-wider">RECORDATORIO</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-blue-600 uppercase tracking-wider">ESTADO</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-blue-600 uppercase tracking-wider">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($pendientes as $pendiente)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-cyan-500">{{ $pendiente->fecha->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-cyan-500">{{ $pendiente->hora->format('g:i A') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate">{{ $pendiente->recordatorio }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $pendiente->activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $pendiente->activo ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <a href="{{ route('pendientes.edit', $pendiente) }}" class="inline-flex items-center justify-center w-9 h-9 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors shadow-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('pendientes.destroy', $pendiente) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este pendiente?');">
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
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            No hay pendientes registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $pendientes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
