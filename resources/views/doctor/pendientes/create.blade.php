<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('pendientes.create') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-6">{{ __('pendientes.create') }}</h2>

                    <form action="{{ route('pendientes.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="recordatorio" class="block text-gray-700 text-sm font-bold mb-2">{{ __('pendientes.fields.reminder') }}</label>
                            <textarea name="recordatorio" id="recordatorio" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>{{ old('recordatorio') }}</textarea>
                            @error('recordatorio')
                                <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="fecha" class="block text-gray-700 text-sm font-bold mb-2">{{ __('pendientes.fields.date') }}</label>
                                <input type="date" name="fecha" id="fecha" value="{{ old('fecha', date('Y-m-d')) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                @error('fecha')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="hora" class="block text-gray-700 text-sm font-bold mb-2">{{ __('pendientes.fields.time') }}</label>
                                <input type="time" name="hora" id="hora" value="{{ old('hora', date('H:i')) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                                @error('hora')
                                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="activo" class="form-checkbox h-5 w-5 text-blue-600" checked>
                                <span class="ml-2 text-gray-700">{{ __('status.active') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button type="submit">
                                {{ __('common.save_record') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
