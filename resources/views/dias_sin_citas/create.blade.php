<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li class="inline-flex items-center">
                        <a href="{{ route('dias-sin-citas.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            {{ __('dias_sin_citas.title') }}
                        </a>
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
                    <h2 class="text-xl font-bold text-gray-800 mb-6">{{ __('dias_sin_citas.form.title') }}</h2>

                    <form action="{{ route('dias-sin-citas.store') }}" method="POST" x-data="{ todoElDia: true }">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 mt-4 sm:grid-cols-2">
                            <!-- Motivo -->
                            <div class="col-span-2">
                                <label for="motivo" class="block text-sm font-medium text-gray-700">{{ __('dias_sin_citas.fields.reason') }}</label>
                                <input type="text" name="motivo" id="motivo" class="mt-1 focus:ring-[#0061F5] focus:border-[#0061F5] block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('motivo') }}" required placeholder="{{ __('dias_sin_citas.form.reason_placeholder') }}">
                                @error('motivo')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Fechas -->
                            <div>
                                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">{{ __('dias_sin_citas.form.start_date') }}</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="mt-1 focus:ring-[#0061F5] focus:border-[#0061F5] block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('fecha_inicio') }}" required>
                                @error('fecha_inicio')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="fecha_fin" class="block text-sm font-medium text-gray-700">{{ __('dias_sin_citas.form.end_date') }}</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="mt-1 focus:ring-[#0061F5] focus:border-[#0061F5] block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('fecha_fin') }}" required>
                                @error('fecha_fin')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Todo el día check -->
                            <div class="col-span-2">
                                <div class="flex items-start">
                                    <div class="flex items-center h-5">
                                        <input id="todo_el_dia" name="todo_el_dia" type="checkbox" value="1" x-model="todoElDia" class="focus:ring-[#0061F5] h-4 w-4 text-[#0061F5] border-gray-300 rounded">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="todo_el_dia" class="font-medium text-gray-700">{{ __('dias_sin_citas.fields.all_day') }}</label>
                                        <p class="text-gray-500">{{ __('dias_sin_citas.form.all_day_help') }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Horarios (Condicional) -->
                            <div x-show="!todoElDia" class="col-span-2 grid grid-cols-1 gap-6 sm:grid-cols-2" x-cloak>
                                <div>
                                    <label for="hora_inicio" class="block text-sm font-medium text-gray-700">{{ __('dias_sin_citas.fields.start_time') }}</label>
                                    <input type="time" name="hora_inicio" id="hora_inicio" class="mt-1 focus:ring-[#0061F5] focus:border-[#0061F5] block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('hora_inicio') }}">
                                    @error('hora_inicio')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="hora_fin" class="block text-sm font-medium text-gray-700">{{ __('dias_sin_citas.fields.end_time') }}</label>
                                    <input type="time" name="hora_fin" id="hora_fin" class="mt-1 focus:ring-[#0061F5] focus:border-[#0061F5] block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('hora_fin') }}">
                                    @error('hora_fin')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Consultorios -->
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('dias_sin_citas.form.apply_to_offices') }}</label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 border rounded-md p-4 max-h-60 overflow-y-auto">
                                    @foreach($consultorios as $consultorio)
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input id="consultorio_{{ $consultorio->id }}" name="consultorios[]" type="checkbox" value="{{ $consultorio->id }}" class="focus:ring-[#0061F5] h-4 w-4 text-[#0061F5] border-gray-300 rounded">
                                            </div>
                                            <div class="ml-3 text-sm">
                                                <label for="consultorio_{{ $consultorio->id }}" class="font-medium text-gray-700">{{ $consultorio->nombre }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('consultorios')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <a href="{{ route('dias-sin-citas.index') }}" class="mr-4 px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded-md hover:bg-gray-300 transition-colors">
                                {{ __('common.buttons.cancel') }}
                            </a>
                            <button type="submit" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                {{ __('common.buttons.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
