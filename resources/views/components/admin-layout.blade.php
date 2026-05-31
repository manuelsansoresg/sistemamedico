<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistema Medico') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            th { white-space: nowrap; }
            .flex.items-center i.fas, .inline-flex.items-center i.fas { line-height: 1; }
            .dashboard-card-label { text-transform: uppercase; display: block; text-align: center; font-weight: 700; }
            nav[aria-label="Breadcrumb"] a,
            nav[aria-label="Breadcrumb"] span { text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.75rem; }
        </style>
    </head>
    <body class="font-sans antialiased bg-[#F8FAFC]">
        <div class="min-h-screen">
            
            <header class="bg-[#27ADFA]">
                <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-14 sm:h-16 flex items-center gap-2 sm:gap-3">
                    <div class="flex flex-none items-center min-w-0">
                        <img src="{{ Auth::user()->profile_photo_url }}"
                             alt="{{ Auth::user()->name }}"
                             class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg object-cover border border-white/40 shrink-0">
                    </div>

                    <div class="flex-1 min-w-0 flex items-center">
                        <a href="{{ route('dashboard') }}" class="truncate text-white font-bold text-base sm:text-lg tracking-tight leading-tight">
                            {{ config('app.name', 'Sistema Medico') }}
                        </a>
                    </div>

                    <div class="flex flex-none items-center justify-end gap-1.5 sm:gap-3">
                        <x-language-switcher />
                        <x-notification-center />
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full text-white hover:bg-white/15 transition-colors" title="{{ __('common.log_out') }}">
                                <i class="fas fa-sign-out-alt text-base sm:text-lg" style="color: #ffffff;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Breadcrumbs Area -->
            @isset($breadcrumbs)
                <div class="bg-white shadow-sm border-b border-gray-100">
                    <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
                        {{ $breadcrumbs }}
                    </div>
                </div>
            @endisset

            <!-- Page Content -->
            <main>
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
                    @if (session('success'))
                        @php
                            $successMessage = session('success');
                            $successText = \Illuminate\Support\Facades\Lang::has($successMessage)
                                ? __($successMessage)
                                : $successMessage;
                        @endphp
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">{{ __('common.success') }}</strong>
                            <span class="block sm:inline">{{ $successText }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        @php
                            $errorMessage = session('error');
                            $errorText = \Illuminate\Support\Facades\Lang::has($errorMessage)
                                ? __($errorMessage)
                                : $errorMessage;
                        @endphp
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">{{ __('common.error') }}</strong>
                            <span class="block sm:inline">{{ $errorText }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">{{ __('common.warning') }}</strong>
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                {{ $slot }}
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
