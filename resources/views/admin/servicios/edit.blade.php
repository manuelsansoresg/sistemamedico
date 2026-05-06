<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li class="inline-flex items-center">
                        <a href="{{ route('servicios.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            {{ __('servicios.title') }}
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('common.breadcrumbs.edit') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">{{ __('servicios.edit') }}</h2>

                    <form action="{{ route('servicios.update', $servicio) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="nombre" class="block text-sm font-bold text-gray-700">{{ __('servicios.fields.name') }}</label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $servicio->nombre) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>

                            <div>
                                <label for="duracion" class="block text-sm font-bold text-gray-700">{{ __('servicios.fields.duration') }}</label>
                                <input type="number" name="duracion" id="duracion" value="{{ old('duracion', $servicio->duracion) }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>

                            <div>
                                <label for="costo" class="block text-sm font-bold text-gray-700">{{ __('servicios.fields.cost') }}</label>
                                <input type="number" name="costo" id="costo" value="{{ old('costo', $servicio->costo) }}" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <a href="{{ route('servicios.index') }}" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors mr-2">{{ __('common.buttons.cancel') }}</a>
                            <x-primary-button>{{ __('common.buttons.save') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
