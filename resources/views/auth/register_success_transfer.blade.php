<x-guest-layout>
    <div class="text-center p-6">
        <div class="mb-6 flex justify-center">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                <i class="fas fa-check-circle text-5xl"></i>
            </div>
        </div>

        <h2 class="mb-4 text-3xl font-bold text-gray-900">{{ __('auth.registration.success.title') }}</h2>
        
        <p class="text-lg text-gray-600 mb-6">
            {{ __('auth.registration.success.transfer_message') }}
        </p>
        
        <p class="text-gray-600 mb-8">
            {{ __('auth.registration.success.transfer_hint') }}
        </p>

        <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 mb-8 text-left inline-block w-full max-w-lg">
            <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">{{ __('auth.registration.sections.bank_transfer_data') }}</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="text-gray-500">{{ __('auth.registration.fields.amount_to_pay') }}</div>
                <div class="text-gray-900 font-bold text-xl">${{ number_format($paquete->precio, 2) }}</div>
                
                <div class="text-gray-500">{{ __('emails.common.bank') }}</div>
                <div class="text-gray-900 font-bold">BBVA</div>
                
                <div class="text-gray-500">{{ __('emails.common.beneficiary') }}</div>
                <div class="text-gray-900 font-bold">Sistema Médico S.A. de C.V.</div>
                
                <div class="text-gray-500">CLABE:</div>
                <div class="text-gray-900 font-mono">012 180 015544332211 5</div>
                
                <div class="text-gray-500">{{ __('auth.registration.fields.concept_reference') }}</div>
                <div class="text-gray-900">{{ auth()->user()->email ?? __('auth.registration.payment.email_reference') }}</div>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('auth.registration.buttons.go_login') }}
            </a>
        </div>
    </div>
</x-guest-layout>
