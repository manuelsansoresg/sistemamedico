<x-guest-layout>
    <div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden text-center p-8">
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            
            <h2 class="text-3xl font-extrabold text-[rgb(0,78,139)] mb-4">
                ¡Pago Validado!
            </h2>
            
            <p class="text-gray-600 mb-8">
                Esta suscripción ya ha sido marcada como pagada. No es necesario realizar más acciones.
            </p>

            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-[rgb(0,78,139)] hover:bg-blue-800">
                Ir al Inicio de Sesión
            </a>
        </div>
    </div>
</x-guest-layout>
