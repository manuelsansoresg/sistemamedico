<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 px-2">
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide text-center">{{ __('dashboard.card_labels.my_profile') }}</span>
                </a>
                <a href="{{ route('paciente.expedientes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-folder-open text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide text-center">{{ __('dashboard.card_labels.records') }}</span>
                </a>
                <a href="{{ route('paciente.qr.show') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-qrcode text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide text-center">{{ __('pacientes.qr.title') }}</span>
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="inline-block w-6 h-6 text-[#0061F5] mr-2"><i class="fas fa-user"></i></span> {{ __('dashboard.patient.profile_title') }}
                        </h3>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm gap-2">
                            <span>{{ __('common.buttons.edit') }}</span><i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <p class="text-gray-600 text-sm">{{ __('dashboard.patient.profile_description') }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="inline-block w-6 h-6 text-[#0061F5] mr-2"><i class="fas fa-folder-open"></i></span> {{ __('dashboard.patient.records_title') }}
                        </h3>
                    </div>

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">{{ __('expedientes.patient.filters.clinic') }}</label>
                            <select name="clinica_id" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]">
                                <option value="">{{ __('expedientes.patient.filters.all_feminine') }}</option>
                                @foreach($clinicas as $c)
                                    <option value="{{ $c->id }}" @selected(request('clinica_id') == $c->id)>{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">{{ __('expedientes.patient.filters.office') }}</label>
                            <select name="consultorio_id" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]">
                                <option value="">{{ __('expedientes.patient.filters.all_masculine') }}</option>
                                @foreach($consultorios as $co)
                                    <option value="{{ $co->id }}" @selected(request('consultorio_id') == $co->id)>{{ $co->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">{{ __('expedientes.patient.filters.from') }}</label>
                            <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">{{ __('expedientes.patient.filters.to') }}</label>
                            <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]" />
                        </div>
                        <div class="md:col-span-4">
                            <button class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                <i class="fas fa-filter mr-2"></i> {{ __('common.buttons.filter') }}
                            </button>
                            @if(request()->hasAny(['clinica_id','consultorio_id','fecha_inicio','fecha_fin']))
                                <a href="{{ route('dashboard') }}" class="ml-3 text-sm text-gray-600 hover:text-gray-800">{{ __('common.buttons.clear') }}</a>
                            @endif
                        </div>
                    </form>

                    @if($expedientes->count() > 0)
                        <form method="POST" action="{{ route('paciente.expedientes.download.bulk') }}">
                            @csrf
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-sm text-gray-600">
                                    {{ __('expedientes.patient.messages.select_to_download_zip') }}
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white text-sm font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                        <i class="fas fa-download mr-2"></i> {{ __('common.buttons.download_selected') }}
                                    </button>
                                    <a href="{{ route('paciente.expedientes.download.all', request()->query()) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-700 rounded-md hover:bg-gray-50 transition-colors shadow-sm">
                                        <i class="fas fa-file-archive mr-2"></i> {{ __('common.buttons.download_all') }}
                                    </a>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                <input type="checkbox" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" onclick="document.querySelectorAll('.expediente-checkbox').forEach(cb => cb.checked = this.checked)">
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.date') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.doctor') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.clinic') }} / {{ __('common.office') }}</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.detail') }}</th>
                                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">PDF</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($expedientes as $consulta)
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <input type="checkbox" name="selected[]" value="{{ $consulta->id }}" class="expediente-checkbox rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ optional($consulta->cita?->fecha)->format('d/m/Y') ?? $consulta->created_at->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $consulta->doctor?->name }} {{ $consulta->doctor?->apellido_paterno }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <div>{{ $consulta->cita?->clinica?->nombre ?? '—' }}</div>
                                                    <div class="text-xs text-gray-500">{{ $consulta->cita?->consultorio?->nombre ?? '—' }}</div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    <div class="text-xs text-gray-500">
                                                        <span class="font-semibold">{{ __('expedientes.patient.table.detail.reason_label') }}</span>
                                                        {{ \Illuminate\Support\Str::limit($consulta->cita?->motivo ?? __('expedientes.patient.table.detail.no_reason'), 60) }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        <span class="font-semibold">{{ __('expedientes.patient.table.detail.template_label') }}</span>
                                                        {{ $consulta->plantilla->nombre ?? __('expedientes.patient.table.detail.no_template') }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        <span class="font-semibold">{{ __('expedientes.patient.table.detail.studies_label') }}</span>
                                                        {{ $consulta->estudios->count() }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <a href="{{ route('paciente.consultas.print', $consulta->id) }}" target="_blank" class="inline-flex items-center justify-center w-10 h-10 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="PDF">
                                                        <i class="fas fa-file-pdf text-xl"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                {{ $expedientes->links() }}
                            </div>
                        </form>
                    @else
                        <p class="text-gray-500 italic">{{ __('expedientes.patient.messages.no_records_yet') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
