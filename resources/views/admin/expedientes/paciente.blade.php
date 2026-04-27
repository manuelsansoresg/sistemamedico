<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('expedientes.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">{{ __('expedientes.title') }}</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('expedientes.patient.breadcrumbs.patient_history') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-[#0061F5]">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ $paciente->name }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ __('expedientes.patient.description') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expedientes.patient.filters.clinic') }}</label>
                        <select name="clinica_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                            <option value="">{{ __('expedientes.patient.filters.all_feminine') }}</option>
                            @foreach($clinicas as $clinica)
                                <option value="{{ $clinica->id }}" @selected(request('clinica_id') == $clinica->id)>{{ $clinica->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expedientes.patient.filters.office') }}</label>
                        <select name="consultorio_id" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                            <option value="">{{ __('expedientes.patient.filters.all_masculine') }}</option>
                            @foreach($consultorios as $consultorio)
                                <option value="{{ $consultorio->id }}" @selected(request('consultorio_id') == $consultorio->id)>{{ $consultorio->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expedientes.patient.filters.from') }}</label>
                        <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('expedientes.patient.filters.to') }}</label>
                        <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                    </div>
                    <div class="md:col-span-4 flex justify-end mt-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0051CC] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5]">
                            <i class="fas fa-filter mr-2"></i> {{ __('common.buttons.filter') }}
                        </button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('expedientes.patient.table.headers.date') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('expedientes.patient.table.headers.clinic') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('expedientes.patient.table.headers.office') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('expedientes.patient.table.headers.doctor') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('expedientes.patient.table.headers.detail') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('expedientes.patient.table.headers.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($expedientes as $expediente)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ optional($expediente->cita->fecha)->format('d/m/Y') }}
                                            <div class="text-xs text-gray-500">
                                                {{ optional($expediente->cita->hora_inicio)->format('H:i') }} - {{ optional($expediente->cita->hora_fin)->format('H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $expediente->cita->clinica->nombre ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $expediente->cita->consultorio->nombre ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $expediente->doctor->name }} {{ $expediente->doctor->apellido_paterno }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="text-xs text-gray-500">
                                                <span class="font-semibold">{{ __('expedientes.patient.table.detail.reason_label') }}</span>
                                                {{ \Illuminate\Support\Str::limit($expediente->cita->motivo ?? __('expedientes.patient.table.detail.no_reason'), 60) }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <span class="font-semibold">{{ __('expedientes.patient.table.detail.template_label') }}</span>
                                                {{ $expediente->plantilla->nombre ?? __('expedientes.patient.table.detail.no_template') }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <span class="font-semibold">{{ __('expedientes.patient.table.detail.studies_label') }}</span>
                                                {{ $expediente->estudios->count() }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <a href="{{ route('consultas.show', $expediente->id) }}" class="inline-flex items-center justify-center w-9 h-9 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors shadow-sm" title="{{ __('expedientes.patient.table.detail.view_consultation') }}">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            {{ __('expedientes.patient.messages.no_consultations_for_filters') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $expedientes->links() }}
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
