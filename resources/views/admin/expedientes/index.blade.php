<x-admin-layout>
    <div class="py-10" x-data="expedienteManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Expedientes</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Expedientes</h2>
                        
                        <div class="flex space-x-2">
                                <!-- Bulk Download Button -->
                                <button 
                                    type="button" 
                                    @click="downloadSelected()"
                                    class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm flex items-center">
                                    <i class="fas fa-download mr-2 text-xl"></i> DESCARGAR SELECCIONADOS
                                </button>

                                @if(auth()->user()->hasRole('root') || auth()->user()->hasRole('doctor') || auth()->user()->can('descargar expedientes') || auth()->user()->can('descargar consultas') || auth()->user()->can('descargar estudios'))
                                <a href="{{ route('expedientes.download.all', request()->query()) }}" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm flex items-center ml-2">
                                    <i class="fas fa-file-archive mr-2 text-xl"></i> DESCARGAR TODO
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                            <div>
                                <label for="clinica_id" class="block text-sm font-medium text-gray-700">Clínica</label>
                                <select name="clinica_id" id="clinica_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                    <option value="">Todas</option>
                                    @foreach($clinicas as $clinica)
                                        <option value="{{ $clinica->id }}" {{ request('clinica_id') == $clinica->id ? 'selected' : '' }}>{{ $clinica->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="consultorio_id" class="block text-sm font-medium text-gray-700">Consultorio</label>
                                <select name="consultorio_id" id="consultorio_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                    <option value="">Todos</option>
                                    @foreach($consultorios as $consultorio)
                                        <option value="{{ $consultorio->id }}" {{ request('consultorio_id') == $consultorio->id ? 'selected' : '' }}>{{ $consultorio->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha Inicio</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                            </div>
                            <div>
                                <label for="fecha_fin" class="block text-sm font-medium text-gray-700">Fecha Fin</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-transparent">Filtrar</label>
                                <button type="submit" class="w-full px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm flex items-center justify-center mt-1">
                                    <i class="fas fa-filter mr-2"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">
                                        <input type="checkbox" x-model="allSelected" @change="toggleAll" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">FECHA</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">PACIENTE</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">DOCTOR</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">CLÍNICA / CONSULTORIO</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-[#0061F5] uppercase tracking-wider">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($expedientes as $expediente)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" value="{{ $expediente->id }}" x-model="selected" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $expediente->cita->fecha->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $expediente->paciente->name }} {{ $expediente->paciente->apellido_paterno }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $expediente->doctor->name }} {{ $expediente->doctor->apellido_paterno }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>{{ $expediente->cita->clinica->nombre }}</div>
                                            <div class="text-xs">{{ $expediente->cita->consultorio->nombre }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                @can('descargar consultas')
                                                <a href="{{ route('consultas.print', $expediente->id) }}" target="_blank" class="inline-flex items-center justify-center w-10 h-10 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="Descargar Consulta">
                                                    <i class="fas fa-file-pdf text-xl"></i>
                                                </a>
                                                @endcan
                                                
                                                @can('descargar estudios')
                                                @if($expediente->estudios->count() > 0)
                                                <button type="button" @click="downloadSingle('{{ $expediente->id }}')" class="inline-flex items-center justify-center w-10 h-10 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition-colors shadow-sm" style="background-color: #1f2937;" title="Descargar Estudios">
                                                    <i class="fas fa-file-medical text-xl"></i>
                                                </button>
                                                @endif
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            No se encontraron expedientes con los filtros seleccionados.
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
                        alert('Por favor selecciona al menos un expediente para descargar.');
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