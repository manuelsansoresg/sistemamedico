<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
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
                            <a href="{{ route('pendientes.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">
                                Pendientes
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Editar Pendiente</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Editar Pendiente</h2>
                        <a href="{{ route('pendientes.index') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                        </a>
                    </div>

                    <form action="{{ route('pendientes.update', $pendiente) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="recordatorio" class="block text-gray-700 text-sm font-bold mb-2">Recordatorio</label>
                            <textarea name="recordatorio" id="recordatorio" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>{{ old('recordatorio', $pendiente->recordatorio) }}</textarea>
                            @error('recordatorio')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="fecha" class="block text-gray-700 text-sm font-bold mb-2">Fecha</label>
                                <input type="date" name="fecha" id="fecha" value="{{ old('fecha', $pendiente->fecha->format('Y-m-d')) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                @error('fecha')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="hora" class="block text-gray-700 text-sm font-bold mb-2">Hora</label>
                                <input type="time" name="hora" id="hora" value="{{ old('hora', $pendiente->hora->format('H:i')) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                @error('hora')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="activo" class="form-checkbox h-5 w-5 text-blue-600" {{ $pendiente->activo ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700">Activo</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button>
                                Actualizar
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
