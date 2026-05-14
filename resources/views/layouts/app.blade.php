<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }
            .dashboard-card-label { text-transform: uppercase; display: block; text-align: center; font-weight: 700; }
            nav[aria-label="Breadcrumb"] a,
            nav[aria-label="Breadcrumb"] span { text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.75rem; }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
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
    </body>
</html>
