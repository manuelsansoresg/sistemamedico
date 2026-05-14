<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Sistema Médico') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8FAFC] text-[#1E293B]">
    <header class="w-full bg-[#27ADFA]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <h1 class="text-white font-extrabold tracking-widest text-2xl">Sistema Médico</h1>
            <nav class="space-x-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-white text-[#0061F5] font-bold rounded-md hover:bg-gray-100 transition-colors">{{ __('public.home.dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-white text-[#0061F5] font-bold rounded-md hover:bg-gray-100 transition-colors">{{ __('public.home.login') }}</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors">{{ __('public.home.register') }}</a>
                    @endif
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16 items-start">
            <div>
                <h2 class="text-5xl font-extrabold text-[#0061F5] mb-6 leading-tight">{{ __('public.home.headline') }}</h2>
                <p class="text-lg text-[#1E293B] mb-8 max-w-xl">{{ __('public.home.subtitle') }}</p>
                <ul class="space-y-3 mb-10">
                    <li class="flex items-center"><i class="fas fa-chevron-right text-[#0061F5] mr-3"></i>{{ __('public.home.features.schedule') }}</li>
                    <li class="flex items-center"><i class="fas fa-chevron-right text-[#0061F5] mr-3"></i>{{ __('public.home.features.patients') }}</li>
                    <li class="flex items-center"><i class="fas fa-chevron-right text-[#0061F5] mr-3"></i>{{ __('public.home.features.records') }}</li>
                </ul>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-7 py-3.5 bg-[#0061F5] text-white font-bold rounded-lg hover:bg-[#0051CC] transition-colors">
                        {{ __('public.home.dashboard') }}
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center px-7 py-3.5 bg-[#0061F5] text-white font-bold rounded-lg hover:bg-[#0051CC] transition-colors mr-3">
                        {{ __('public.home.create_account') }}
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center px-7 py-3.5 bg-white text-[#0061F5] font-bold rounded-lg border border-[#0061F5] hover:bg-gray-50 transition-colors">
                        {{ __('public.home.login') }}
                    </a>
                @endauth
            </div>
            <div class="bg-white rounded-2xl shadow p-8 border border-gray-100 w-full">
                <div class="grid grid-cols-2 gap-8">
                    <a href="{{ route('login') }}" class="p-5 rounded-xl bg-blue-50 hover:bg-blue-100 transition flex items-center justify-between">
                        <div class="font-bold text-[#1E293B]">{{ __('public.home.cards.appointments') }}</div>
                        <span class="w-9 h-9 rounded-full bg-white text-[#0061F5] flex items-center justify-center"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <a href="{{ route('login') }}" class="p-5 rounded-xl bg-blue-50 hover:bg-blue-100 transition flex items-center justify-between">
                        <div class="font-bold text-[#1E293B]">{{ __('public.home.cards.patients') }}</div>
                        <span class="w-9 h-9 rounded-full bg-white text-[#0061F5] flex items-center justify-center"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <a href="{{ route('login') }}" class="p-5 rounded-xl bg-blue-50 hover:bg-blue-100 transition flex items-center justify-between">
                        <div class="font-bold text-[#1E293B]">{{ __('public.home.cards.records') }}</div>
                        <span class="w-9 h-9 rounded-full bg-white text-[#0061F5] flex items-center justify-center"><i class="fas fa-chevron-right"></i></span>
                    </a>
                    <a href="{{ route('login') }}" class="p-5 rounded-xl bg-blue-50 hover:bg-blue-100 transition flex items-center justify-between">
                        <div class="font-bold text-[#1E293B]">{{ __('public.home.cards.clinics') }}</div>
                        <span class="w-9 h-9 rounded-full bg-white text-[#0061F5] flex items-center justify-center"><i class="fas fa-chevron-right"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer class="py-8 text-center text-sm text-gray-500">
        © {{ date('Y') }} {{ config('app.name', 'Sistema Médico') }} - {{ __('public.home.rights') }}
    </footer>
</body>
</html>
