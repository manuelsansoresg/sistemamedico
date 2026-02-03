<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Estatus de Cuenta') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 text-center">
                    @if(auth()->user()->estatus_cedula === 'pendiente')
                        <div class="mb-4">
                            <svg class="mx-auto h-16 w-16 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Validación de Cédula Pendiente</h3>
                        <div class="max-w-xl mx-auto text-gray-600 space-y-2">
                            <p>
                                Tu cuenta está restringida temporalmente porque elegiste un paquete que requiere validación de cédula profesional.
                            </p>
                            <p>
                                Nuestro equipo administrativo está revisando tu información. Te notificaremos una vez que tu cédula haya sido validada y tendrás acceso completo al panel.
                            </p>
                        </div>
                    @elseif(auth()->user()->estatus_cedula === 'rechazada')
                        <div class="mb-4">
                            <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-red-600 mb-2">Validación de Cédula Rechazada</h3>
                        <div class="max-w-xl mx-auto text-gray-600">
                            <p>
                                Lamentablemente, tu cédula profesional no pudo ser validada.
                            </p>
                            <p class="mt-2">
                                Por favor contacta a soporte técnico para aclarar la situación o actualizar tus datos.
                            </p>
                        </div>
                    @else
                        {{-- Should not happen if middleware works correctly --}}
                        <p>Tu estatus es: {{ auth()->user()->estatus_cedula }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
