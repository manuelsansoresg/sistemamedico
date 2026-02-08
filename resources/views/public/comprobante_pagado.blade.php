<x-guest-layout maxWidth="sm:max-w-2xl">
    <x-slot name="header">
        <div class="bg-blue-900 py-6 text-center">
            <h1 class="text-2xl font-bold text-white tracking-wider">
                {{ config('app.name', 'Sistema Médico') }}
            </h1>
        </div>
    </x-slot>

    <div class="py-8 text-center">
        <div class="mb-6">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
        </div>
        
        <h2 class="text-3xl font-extrabold text-blue-900 mb-4">
            ¡Pago Validado!
        </h2>
        
        <p class="text-gray-600 mb-8">
            Esta suscripción ya ha sido marcada como pagada. No es necesario realizar más acciones.
        </p>

        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-800 hover:bg-blue-900">
            Ir al Inicio de Sesión
        </a>
    </div>
</x-guest-layout>
