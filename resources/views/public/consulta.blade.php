<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('public.expediente.consultation_detail') }} - {{ config('app.name', 'Sistema Medico') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8FAFC] font-sans antialiased text-[#1E293B]">
        <main class="min-h-screen py-8">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div>
                    <a href="{{ route('public.expediente.show', $token) }}" class="inline-flex items-center text-sm font-semibold text-[#0061F5] hover:text-[#0051CC]">
                        <i class="fas fa-arrow-left mr-2"></i>{{ __('public.expediente.back_to_record') }}
                    </a>
                </div>

                <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-[#0061F5]">
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-[#0061F5]">
                        {{ __('public.expediente.readonly_badge') }}
                    </span>
                    <h1 class="mt-3 text-3xl font-bold text-gray-900">{{ __('public.expediente.consultation_detail') }}</h1>
                    <div class="mt-3 flex flex-wrap gap-4 text-sm text-gray-600">
                        <span><i class="fas fa-user mr-2 text-[#0061F5]"></i>{{ $paciente->name }} {{ $paciente->apellido_paterno }}</span>
                        <span><i class="fas fa-user-md mr-2 text-[#0061F5]"></i>{{ $consulta->doctor?->name }} {{ $consulta->doctor?->apellido_paterno }}</span>
                        <span><i class="fas fa-calendar-alt mr-2 text-[#0061F5]"></i>{{ optional($consulta->cita?->fecha)->format('d/m/Y') ?? $consulta->created_at->format('d/m/Y') }}</span>
                    </div>
                </section>

                <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('consultas.fields.weight') }}</label>
                            <div class="px-3 py-2 border border-gray-200 rounded-md bg-gray-50">{{ $consulta->peso ?? '—' }} kg</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('consultas.fields.height') }}</label>
                            <div class="px-3 py-2 border border-gray-200 rounded-md bg-gray-50">{{ $consulta->estatura ?? '—' }} m</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">{{ __('consultas.fields.allergies') }}</label>
                            <div class="px-3 py-2 border border-gray-200 rounded-md bg-gray-50">{{ $consulta->alergias ?: '—' }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('consultas.fields.template') }}</label>
                        <div class="px-3 py-2 border border-gray-200 rounded-md bg-gray-50">{{ $consulta->plantilla?->nombre ?? '—' }}</div>
                    </div>

                    @if($consulta->valores && $consulta->valores->count() > 0)
                        <div class="space-y-4">
                            @foreach($consulta->valores as $valor)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">{{ $valor->campo?->etiqueta ?? __('common.detail') }}</label>
                                    <div class="mt-1 px-3 py-2 border border-gray-200 rounded-md bg-gray-50 whitespace-pre-line">{{ $valor->valor }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">{{ __('consultas.messages.no_captured_values') }}</p>
                    @endif

                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-microscope text-[#0061F5] mr-2"></i>{{ __('consultas.tabs.studies') }}
                        </h2>

                        @if($consulta->estudios && $consulta->estudios->count() > 0)
                            <div class="space-y-4">
                                @foreach($consulta->estudios as $estudio)
                                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                        <p class="text-sm font-semibold text-gray-800">{{ __('consultas.fields.study_order') }}</p>
                                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $estudio->orden }}</p>

                                        @if($estudio->observacion)
                                            <div class="mt-2">
                                                <p class="text-xs font-semibold text-gray-600">{{ __('consultas.fields.observations') }}</p>
                                                <p class="text-xs text-gray-700 whitespace-pre-line">{{ $estudio->observacion }}</p>
                                            </div>
                                        @endif

                                        @if($estudio->archivos && $estudio->archivos->count() > 0)
                                            <div class="mt-3">
                                                <p class="text-xs font-semibold text-gray-600 mb-1">{{ __('consultas.sections.files') }}</p>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($estudio->archivos as $archivo)
                                                        <a href="{{ asset($archivo->path) }}" target="_blank" class="inline-flex items-center px-2 py-1 text-xs bg-white border border-gray-300 rounded-md text-[#0061F5] hover:bg-gray-50">
                                                            <i class="fas fa-paperclip mr-1"></i>{{ $archivo->nombre_original }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">{{ __('consultas.messages.no_registered_studies') }}</p>
                        @endif
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>
