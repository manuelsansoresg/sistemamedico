<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ __('public.expediente.title') }} - {{ config('app.name', 'Sistema Medico') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#F8FAFC] font-sans antialiased text-[#1E293B]">
        <main class="min-h-screen py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-[#0061F5]">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <span class="inline-flex items-center rounded-full bg-[#27ADFA] px-3 py-1 text-xs font-semibold uppercase tracking-wide text-slate-800">
                                {{ __('public.expediente.readonly_badge') }}
                            </span>

                            <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                                {{ __('public.expediente.title') }}
                            </h1>

                            <p class="mt-3 flex items-center gap-2 text-base text-gray-500 sm:text-lg">
                                <i class="fas fa-qrcode text-sm text-gray-500"></i>
                                {{ __('public.expediente.shared_by_qr') }}
                            </p>
                        </div>

                        <img src="{{ $paciente->profile_photo_url }}" alt="{{ __('pacientes.profile_photo_alt') }}" class="h-20 w-20 flex-shrink-0 rounded-full border-4 border-white object-cover shadow-sm ring-1 ring-gray-200 sm:h-24 sm:w-24">
                    </div>
                </section>

                <div class="w-full rounded-2xl border border-[#F5C994] bg-[#FBF4EA] px-6 py-6 text-[#4B2B05] shadow-none sm:px-7">
                    <div class="flex items-start gap-4 sm:items-center">
                        <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center text-[#8A5A05] sm:mt-0">
                            <i class="fas fa-info-circle text-[24px] leading-none"></i>
                        </div>

                        <p class="m-0 text-sm font-normal leading-5 text-[#4B2B05] sm:text-base sm:leading-6">
                            {{ __('public.expediente.privacy_notice') }}
                        </p>
                    </div>
                </div>

                <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">{{ __('public.expediente.patient_data') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="block text-gray-500">{{ __('common.name') }}</span>
                            <span class="font-semibold">{{ $paciente->name }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">{{ __('common.birth_date') }}</span>
                            <span class="font-semibold">{{ optional($paciente->fecha_nacimiento)->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-gray-500">{{ __('common.gender') }}</span>
                            <span class="font-semibold">{{ $paciente->sexo ?? '—' }}</span>
                        </div>
                    </div>
                </section>

                <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-800">{{ __('public.expediente.records') }}</h2>
                    </div>

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">{{ __('expedientes.patient.filters.clinic') }}</label>
                            <select name="clinica_id" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]">
                                <option value="">{{ __('expedientes.patient.filters.all_feminine') }}</option>
                                @foreach($clinicas as $clinica)
                                    <option value="{{ $clinica->id }}" @selected(request('clinica_id') == $clinica->id)>{{ $clinica->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">{{ __('expedientes.patient.filters.office') }}</label>
                            <select name="consultorio_id" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]">
                                <option value="">{{ __('expedientes.patient.filters.all_masculine') }}</option>
                                @foreach($consultorios as $consultorio)
                                    <option value="{{ $consultorio->id }}" @selected(request('consultorio_id') == $consultorio->id)>{{ $consultorio->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">{{ __('expedientes.patient.filters.from') }}</label>
                            <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">{{ __('expedientes.patient.filters.to') }}</label>
                            <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]">
                        </div>
                        <div class="md:col-span-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                <i class="fas fa-filter mr-2"></i>{{ __('common.buttons.filter') }}
                            </button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.date') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.doctor') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.clinic') }} / {{ __('common.office') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.detail') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($expedientes as $expediente)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($expediente->cita?->fecha)->format('d/m/Y') ?? $expediente->created_at->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $expediente->doctor?->name }} {{ $expediente->doctor?->apellido_paterno }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>{{ $expediente->cita?->clinica?->nombre ?? '—' }}</div>
                                            <div class="text-xs text-gray-500">{{ $expediente->cita?->consultorio?->nombre ?? '—' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <div class="text-xs"><span class="font-semibold">{{ __('expedientes.patient.table.detail.reason_label') }}</span> {{ \Illuminate\Support\Str::limit($expediente->cita?->motivo ?? __('expedientes.patient.table.detail.no_reason'), 60) }}</div>
                                            <div class="text-xs mt-1"><span class="font-semibold">{{ __('expedientes.patient.table.detail.template_label') }}</span> {{ $expediente->plantilla->nombre ?? __('expedientes.patient.table.detail.no_template') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('public.expediente.consultas.show', [$token, $expediente]) }}" class="inline-flex items-center justify-center w-9 h-9 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors shadow-sm" title="{{ __('common.buttons.view') }}">
                                                <i class="fas fa-eye text-sm"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">{{ __('public.expediente.no_records') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $expedientes->links() }}</div>
                </section>
            </div>
        </main>
    </body>
</html>
