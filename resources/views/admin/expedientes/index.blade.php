<x-admin-layout>
    <div class="py-10" x-data="expedienteManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-700 md:ml-2">{{ __('expedientes.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">{{ __('expedientes.title') }}</h2>
                        
                        <div class="flex space-x-2">
                                <button 
                                    type="button" 
                                    @click="downloadSelected()"
                                    class="px-4 py-2 bg-white text-[#0061F5] text-sm font-bold rounded-md border border-[#0061F5] hover:bg-blue-50 transition-colors shadow-sm flex items-center">
                                    <i class="fas fa-download mr-2"></i> {{ __('common.buttons.download_selected') }}
                                </button>

                                @if(auth()->user()->hasRole('root') || auth()->user()->hasRole('doctor') || auth()->user()->can('descargar expedientes') || auth()->user()->can('descargar consultas') || auth()->user()->can('descargar estudios'))
                                <a href="{{ route('expedientes.download.all', request()->query()) }}" class="px-4 py-2 bg-white text-[#0061F5] text-sm font-bold rounded-md border border-[#0061F5] hover:bg-blue-50 transition-colors shadow-sm flex items-center ml-2">
                                    <i class="fas fa-file-archive mr-2"></i> {{ __('common.buttons.download_all') }}
                                </a>
                                @endif
                            </div>
                    </div>

                    <!-- Hidden Form for Bulk Download -->
                    <form id="bulk-download-form" action="{{ route('expedientes.download.bulk') }}" method="POST" style="display: none;">
                        @csrf
                        <template x-for="id in selected">
                            <input type="hidden" name="selected[]" :value="id">
                        </template>
                    </form>

                    <!-- Filters -->
                    <form method="GET" action="{{ route('expedientes.index') }}" class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                            <div>
                                <label for="clinica_id" class="block text-sm font-medium text-gray-700">{{ __('common.clinic') }}</label>
                                <select name="clinica_id" id="clinica_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                    <option value="">{{ __('common.all') }}</option>
                                    @foreach($clinicas as $clinica)
                                        <option value="{{ $clinica->id }}" {{ request('clinica_id') == $clinica->id ? 'selected' : '' }}>{{ $clinica->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="consultorio_id" class="block text-sm font-medium text-gray-700">{{ __('common.office') }}</label>
                                <select name="consultorio_id" id="consultorio_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                    <option value="">{{ __('common.all') }}</option>
                                    @foreach($consultorios as $consultorio)
                                        <option value="{{ $consultorio->id }}" {{ request('consultorio_id') == $consultorio->id ? 'selected' : '' }}>{{ $consultorio->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="paciente_id" class="block text-sm font-medium text-gray-700">{{ __('common.patient') }}</label>
                                <select name="paciente_id" id="paciente_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                    <option value="">{{ __('common.all') }}</option>
                                    @foreach($pacientes as $paciente)
                                        <option value="{{ $paciente->id }}" {{ request('paciente_id') == $paciente->id ? 'selected' : '' }}>
                                            {{ $paciente->name }} {{ $paciente->apellido_paterno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">{{ __('earnings.filters.date_start') }}</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                            </div>
                            <div>
                                <label for="fecha_fin" class="block text-sm font-medium text-gray-700">{{ __('earnings.filters.date_end') }}</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-transparent">{{ __('common.buttons.filter') }}</label>
                                <button type="submit" class="w-full px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm flex items-center justify-center mt-1">
                                    <i class="fas fa-filter mr-2"></i> {{ __('common.buttons.filter') }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        <input type="checkbox" x-model="allSelected" @change="toggleAll" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.date') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.patient') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.doctor') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.clinic') }} / {{ __('common.office') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.detail') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($expedientes as $expediente)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" value="{{ $expediente->id }}" x-model="selected" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $expediente->cita->fecha->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                            <a href="{{ route('expedientes.paciente', $expediente->paciente_id) }}" class="text-[#0061F5] hover:underline">
                                                {{ $expediente->paciente->name }} {{ $expediente->paciente->apellido_paterno }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $expediente->doctor->name }} {{ $expediente->doctor->apellido_paterno }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>{{ $expediente->cita->clinica->nombre }}</div>
                                            <div class="text-xs text-gray-500">{{ $expediente->cita->consultorio->nombre }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="text-xs text-gray-500">
                                                <span class="font-semibold">{{ __('common.reason') }}:</span>
                                                {{ \Illuminate\Support\Str::limit($expediente->cita->motivo ?? __('common.none'), 60) }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <span class="font-semibold">{{ __('plantillas.title') }}:</span>
                                                {{ $expediente->plantilla->nombre ?? __('common.none') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <a href="{{ route('consultas.show', $expediente->id) }}" class="inline-flex items-center justify-center w-10 h-10 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors shadow-sm" title="{{ __('common.buttons.view') }}">
                                                    <i class="fas fa-eye text-lg"></i>
                                                </a>

                                                @can('descargar consultas')
                                                <a href="{{ route('consultas.print', $expediente->id) }}" target="_blank" class="inline-flex items-center justify-center w-10 h-10 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="{{ __('common.buttons.download') }}">
                                                    <i class="fas fa-file-pdf text-xl"></i>
                                                </a>
                                                @endcan
                                                
                                                @can('descargar estudios')
                                                @if($expediente->estudios->count() > 0)
                                                <button type="button" @click="downloadSingle('{{ $expediente->id }}')" class="inline-flex items-center justify-center w-10 h-10 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition-colors shadow-sm" style="background-color: #1f2937;" title="{{ __('common.buttons.download') }}">
                                                    <i class="fas fa-file-medical text-xl"></i>
                                                </button>
                                                @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            {{ __('common.no_results') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $expedientes->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function expedienteManager() {
            return {
                selected: [],
                allSelected: false,
                toggleAll() {
                    if (this.allSelected) {
                        // Select all checkboxes in the DOM that are bound to x-model="selected"
                        // excluding the "select all" checkbox itself
                        const checkboxes = document.querySelectorAll('input[type="checkbox"][x-model="selected"]');
                        this.selected = Array.from(checkboxes).map(cb => cb.value);
                    } else {
                        this.selected = [];
                    }
                },
                downloadSelected() {
                    if (this.selected.length === 0) {
                        alert("{{ __('expedientes.download') }}");
                        return;
                    }
                    this.$nextTick(() => {
                        document.getElementById('bulk-download-form').submit();
                    });
                },
                downloadSingle(id) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('expedientes.download.bulk') }}";
                    form.style.display = 'none';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'selected[]';
                    idInput.value = id;
                    form.appendChild(idInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    </script>
</x-admin-layout>
