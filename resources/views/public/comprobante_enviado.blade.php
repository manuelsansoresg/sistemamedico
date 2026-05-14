<x-guest-layout maxWidth="sm:max-w-2xl">
    <x-slot name="header">
        <div class="py-6 text-center" style="background-color: #003366;">
            <h1 class="text-2xl font-bold text-white tracking-wider">
                {{ config('app.name', 'Sistema Médico') }}
            </h1>
        </div>
    </x-slot>

    <div class="py-8 text-center">
        <div class="mb-6">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <svg class="h-6 w-6" style="color: #003366;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </div>
        </div>
        
        <h2 class="text-3xl font-extrabold mb-4" style="color: #003366;">
            {{ __('public.receipt.sent_title') }}
        </h2>
        
        <p class="text-gray-600 mb-4 text-lg">
            {{ __('public.receipt.sent_message') }}
        </p>
        
        <p class="text-gray-500 mb-8 max-w-md mx-auto">
            {{ __('public.receipt.sent_hint') }}
        </p>

        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white hover:bg-opacity-90" style="background-color: #003366;">
            {{ __('public.receipt.go_home') }}
        </a>
    </div>
</x-guest-layout>
