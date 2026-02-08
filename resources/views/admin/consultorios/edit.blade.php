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
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('consultorios.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">Consultorios</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Editar</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">¡Ups! Algo salió mal.</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('consultorios.update', $consultorio) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label for="nombre" class="block text-sm font-bold text-gray-700">Nombre</label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $consultorio->nombre) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label for="telefono" class="block text-sm font-bold text-gray-700">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $consultorio->telefono) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>

                            <!-- Activo -->
                            <div class="flex items-center">
                                <input type="checkbox" name="activo" id="activo" value="1" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" {{ old('activo', $consultorio->activo) ? 'checked' : '' }}>
                                <label for="activo" class="ml-2 block text-sm font-medium text-gray-700">Activo</label>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <a href="{{ route('consultorios.index') }}" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors mr-2">Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
