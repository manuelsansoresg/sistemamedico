<x-guest-layout>
    <div class="text-center p-6">
        <div class="mb-6 flex justify-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                <i class="fas fa-check-circle text-5xl"></i>
            </div>
        </div>

        <h2 class="mb-4 text-3xl font-bold text-gray-900">¡Registro Exitoso!</h2>
        
        <p class="lead text-lg text-gray-600 mb-4">
            El pago se ha procesado correctamente. Por favor, revise su correo electrónico para activar su cuenta y comenzar a usar nuestros servicios.
        </p>
        
        <p class="text-muted text-gray-500 mb-8">
            <small>Si no encuentra el correo, revise su bandeja de spam o correo no deseado.</small>
        </p>

        <div class="mt-4">
            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Ir al Inicio de Sesión
            </a>
        </div>
    </div>
</x-guest-layout>