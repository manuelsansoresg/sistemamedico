<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('pacientes.qr.permissions.access_title') }} - {{ config('app.name', 'Sistema Medico') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8FAFC] font-sans antialiased text-[#1E293B]">
        <main class="min-h-screen py-10">
            <div class="mx-auto max-w-xl px-4">
                <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-full bg-[#E6F0FF] text-[#0061F5]">
                        <i class="fas fa-lock text-2xl"></i>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900">{{ __('pacientes.qr.permissions.access_title') }}</h1>
                    <p class="mt-2 text-sm leading-6 text-gray-600">{{ __('pacientes.qr.permissions.access_help') }}</p>

                    <form method="GET" action="{{ route('public.expediente.show', $token) }}" class="mt-6 space-y-4">
                        <div @auth class="hidden" @endauth>
                            <label for="access_code" class="block text-sm font-semibold text-gray-700">{{ __('pacientes.qr.permissions.temporary_code') }}</label>
                            <input id="access_code" name="access_code" type="text" value="{{ request('access_code') }}" class="mt-1 w-full rounded-md border-gray-300 text-sm uppercase tracking-wide focus:border-[#0061F5] focus:ring-[#0061F5]" autocomplete="one-time-code">
                        </div>

                        @if(request('access_code'))
                            <input type="hidden" name="access_code" value="{{ request('access_code') }}">
                        @endif

                        @isset($permission)
                            <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm leading-6 text-gray-700">
                                <input type="checkbox" name="accept_terms" value="1" class="mt-1 rounded border-gray-300 text-[#0061F5] focus:ring-[#0061F5]" required>
                                <span>
                                    @auth
                                        {{ __('pacientes.qr.permissions.doctor_terms') }}
                                    @else
                                        {{ __('pacientes.qr.permissions.external_terms') }}
                                    @endauth
                                </span>
                            </label>
                        @endisset

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-[#0061F5] px-4 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-[#0051CC]">
                            <i class="fas fa-unlock-alt mr-2"></i>{{ __('pacientes.qr.permissions.access_submit') }}
                        </button>
                    </form>

                    <div class="mt-6 rounded-lg border border-[#F5C994] bg-[#FBF4EA] p-4 text-sm leading-6 text-[#4B2B05]">
                        <i class="fas fa-info-circle mr-2"></i>{{ __('pacientes.qr.permissions.doctor_login_help') }}
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>
