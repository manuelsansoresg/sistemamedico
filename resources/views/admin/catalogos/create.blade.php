<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('catalogos.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">{{ __('catalogos.title') }}</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('common.breadcrumbs.create') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-lg font-bold text-cyan-500 mb-6">{{ __('catalogos.form.required_note') }}</h2>

                    <form action="{{ route('catalogos.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nombre -->
                            <div>
                                <label for="nombre" class="block text-sm font-bold text-gray-700 uppercase">*{{ __('catalogos.fields.name') }}</label>
                                <input type="text" name="nombre" id="nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>

                            <!-- Precio -->
                            <div>
                                <label for="precio" class="block text-sm font-bold text-gray-700 uppercase">*{{ __('catalogos.fields.price') }}</label>
                                <input type="number" step="0.01" name="precio" id="precio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>

                            <!-- Porcentaje Ganancia -->
                            <div>
                                <label for="porcentaje_ganancia" class="block text-sm font-bold text-gray-700 uppercase">{{ __('catalogos.fields.profit_percentage') }}</label>
                                <input type="number" step="0.01" name="porcentaje_ganancia" id="porcentaje_ganancia" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>

                            <!-- Descripción -->
                            <div class="md:col-span-2">
                                <label for="descripcion" class="block text-sm font-bold text-gray-700 uppercase">{{ __('catalogos.fields.description') }}</label>
                                <textarea name="descripcion" id="descripcion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]"></textarea>
                            </div>

                            <!-- Activo -->
                            <div class="md:col-span-2">
                                <label for="activo" class="block text-sm font-bold text-gray-700 uppercase">*{{ __('status.active') }}</label>
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="activo" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" checked>
                                        <span class="ml-2 text-gray-600">{{ __('status.active') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <x-primary-button>
                                {{ __('common.buttons.save') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
