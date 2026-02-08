<x-admin-layout>
    <div class="py-12" x-data="consultaHandler()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Breadcrumbs -->
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('citas.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">Citas</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Nueva Consulta</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Patient Header Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-[#0061F5]">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ $paciente->name }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                        </h2>
                        <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-600">
                            <span class="flex items-center"><i class="fas fa-birthday-cake mr-2 text-[#0061F5]"></i> {{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->format('d/m/Y') : 'N/A' }}</span>
                            <span class="flex items-center"><i class="fas fa-user-clock mr-2 text-[#0061F5]"></i> {{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->age . ' años' : 'N/A' }}</span>
                            <span class="flex items-center"><i class="fas fa-venus-mars mr-2 text-[#0061F5]"></i> {{ $paciente->sexo == 'M' ? 'Masculino' : 'Femenino' }}</span>
                            <span class="flex items-center"><i class="fas fa-clinic-medical mr-2 text-[#0061F5]"></i> <span class="text-gray-500 mr-1">Consultorio:</span> {{ $cita->consultorio->nombre }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">EN CONSULTA</span>
                        <p class="text-xs text-gray-500 mt-1">{{ now()->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Health Metrics (Editable) -->
            <div class="bg-[#E6F0FF] overflow-hidden shadow-sm sm:rounded-lg p-6 border border-[#CCE0FF]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-[#004499] flex items-center">
                        <i class="fas fa-heartbeat mr-2"></i> Signos Vitales y Alergias
                    </h3>
                    @if($historialConsultas->count() > 0)
                    <button @click="showHistory = true" type="button" class="text-sm text-[#0061F5] hover:text-[#004499] font-medium flex items-center transition-colors duration-150">
                        <i class="fas fa-history mr-1"></i>
                        Ver Historial
                    </button>
                    @endif
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Peso</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="number" step="0.01" x-model="peso" class="focus:ring-[#0061F5] focus:border-[#0061F5] flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300" placeholder="0.00">
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                kg
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estatura</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="number" step="0.01" x-model="estatura" class="focus:ring-[#0061F5] focus:border-[#0061F5] flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300" placeholder="0.00">
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                m
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alergias</label>
                        <textarea x-model="alergias" rows="1" class="shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Ninguna conocida..."></textarea>
                    </div>
                </div>
            </div>

            <!-- History Modal -->
            <div x-show="showHistory" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div x-show="showHistory" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="showHistory" 
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             @click.away="showHistory = false"
                             class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Historial de Signos Vitales</h3>
                                    <div class="mt-4 overflow-x-auto max-h-[60vh]">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50 sticky top-0">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peso</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatura</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($historialConsultas as $hConsulta)
                                                    @if($hConsulta->peso || $hConsulta->estatura)
                                                    <tr>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $hConsulta->created_at->format('d/m/Y') }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $hConsulta->peso ? $hConsulta->peso . ' kg' : '-' }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $hConsulta->estatura ? $hConsulta->estatura . ' m' : '-' }}</td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @if($historialConsultas->whereNotNull('peso')->isEmpty() && $historialConsultas->whereNotNull('estatura')->isEmpty())
                                            <p class="text-sm text-gray-500 text-center py-4">No hay registros previos de signos vitales.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="button" @click="showHistory = false" class="inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:ml-3 sm:w-auto">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex" aria-label="Tabs">
                        <button @click="activeTab = 'consulta'" 
                            :class="{'border-[#0061F5] text-[#0061F5]': activeTab === 'consulta', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'consulta'}"
                            class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            <i class="fas fa-notes-medical mr-2"></i> Consulta
                        </button>
                        <button @click="activeTab = 'estudios'"
                            :class="{'border-[#0061F5] text-[#0061F5]': activeTab === 'estudios', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'estudios'}"
                            class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            <i class="fas fa-microscope mr-2"></i> Estudios
                        </button>
                    </nav>
                </div>

                <!-- Tab Content: Consulta -->
                <div x-show="activeTab === 'consulta'" class="p-6">
                    <form action="{{ route('consultas.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="cita_id" value="{{ $cita->id }}">
                        <input type="hidden" name="peso" :value="peso">
                        <input type="hidden" name="estatura" :value="estatura">
                        <input type="hidden" name="alergias" :value="alergias">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Plantilla</label>
                            <select name="plantilla_id" x-model="selectedPlantillaId" @change="loadPlantillaCampos()" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                                <option value="">Seleccione una plantilla</option>
                                @foreach($plantillas as $plantilla)
                                    <option value="{{ $plantilla->id }}">{{ $plantilla->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dynamic Fields Area -->
                        <div class="space-y-4 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200 min-h-[200px]" id="campos-container">
                            <template x-if="!selectedPlantillaId">
                                <div class="text-center text-gray-500 py-10">
                                    <i class="fas fa-file-medical text-4xl mb-2 opacity-50"></i>
                                    <p>Seleccione una plantilla para comenzar a llenar la consulta.</p>
                                </div>
                            </template>
                            
                            <template x-for="campo in campos" :key="campo.id">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" x-text="campo.etiqueta"></label>
                                    
                                    <!-- Text Input -->
                                    <template x-if="campo.tipo === 'text'">
                                        <input type="text" :name="'valores[' + campo.id + ']'" class="mt-1 focus:ring-[#0061F5] focus:border-[#0061F5] block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </template>
                                    
                                    <!-- Textarea -->
                                    <template x-if="campo.tipo === 'textarea'">
                                        <textarea :name="'valores[' + campo.id + ']'" rows="3" class="mt-1 shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </template>
                                    
                                    <!-- Number -->
                                    <template x-if="campo.tipo === 'number'">
                                        <input type="number" :name="'valores[' + campo.id + ']'" class="mt-1 focus:ring-[#0061F5] focus:border-[#0061F5] block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </template>
                                    
                                    <!-- Select -->
                                    <template x-if="campo.tipo === 'select'">
                                        <select :name="'valores[' + campo.id + ']'" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                                            <template x-for="opcion in campo.opciones.split(',')" :key="opcion">
                                                <option :value="opcion.trim()" x-text="opcion.trim()"></option>
                                            </template>
                                        </select>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0051CC] active:bg-[#004499] focus:outline-none focus:border-[#004499] focus:ring ring-[#80B0FA] disabled:opacity-25 transition ease-in-out duration-150">
                                <i class="fas fa-save mr-2"></i> Guardar Consulta
                            </button>
                        </div>
                    </form>

                    <!-- History List -->
                    <div class="mt-10">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Historial de Consultas</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plantilla</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($historialConsultas as $historia)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $historia->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $historia->doctor->name }} {{ $historia->doctor->apellido_paterno }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $historia->plantilla->nombre }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                <a href="{{ route('consultas.edit', $historia) }}" class="inline-flex items-center justify-center w-10 h-10 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors shadow-sm" title="Editar">
                                                    <i class="fas fa-edit text-xl"></i>
                                                </a>
                                                <a href="{{ route('consultas.print', $historia) }}" target="_blank" class="inline-flex items-center justify-center w-10 h-10 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition-colors shadow-sm" style="background-color: #1f2937;" title="Imprimir">
                                                    <i class="fas fa-print text-xl"></i>
                                                </a>
                                                <form action="{{ route('consultas.destroy', $historia) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar esta consulta?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center justify-center w-10 h-10 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="Borrar">
                                                        <i class="fas fa-trash text-xl"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay consultas previas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Estudios -->
                <div x-show="activeTab === 'estudios'" class="p-6" style="display: none;">
                    
                    @if($historialConsultas->count() > 0)
                        <!-- Select which consulta to attach study to, usually the latest one -->
                        <form action="{{ route('consultas.estudios.store', $historialConsultas->first()->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Orden de Estudio</label>
                                <textarea name="orden" rows="4" class="mt-1 shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Escriba los estudios requeridos..."></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                                <textarea name="observacion" rows="2" class="mt-1 shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Observaciones adicionales..."></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Adjuntar Imágenes/Archivos (Opcional)</label>
                                
                                <div 
                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md transition-colors duration-200"
                                    :class="{ 'border-[#0061F5] bg-[#E6F0FF]': isDragging }"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop($event)"
                                >
                                    <div class="space-y-1 text-center">
                                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2" :class="{ 'text-[#0061F5]': isDragging }"></i>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-[#0061F5] hover:text-[#0061F5] focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-[#0061F5]">
                                                <span>Subir archivos</span>
                                                <input id="file-upload" name="archivos[]" type="file" class="sr-only" multiple accept="image/*,.pdf" x-ref="fileInput" @change="handleFiles($event)">
                                            </label>
                                            <p class="pl-1">o arrastrar y soltar</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, PDF hasta 5MB</p>
                                    </div>
                                </div>

                                <!-- File List -->
                                <div class="mt-4 space-y-2" x-show="filesArray.length > 0">
                                    <template x-for="(file, index) in filesArray" :key="index">
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md border border-gray-200">
                                            <div class="flex items-center space-x-2 truncate">
                                                <i class="fas fa-file text-gray-400"></i>
                                                <span class="text-sm text-gray-600 truncate" x-text="file.name"></span>
                                                <span class="text-xs text-gray-400" x-text="(file.size / 1024).toFixed(2) + ' KB'"></span>
                                            </div>
                                            <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0051CC] active:bg-[#004499] focus:outline-none focus:border-[#004499] focus:ring ring-[#80B0FA] disabled:opacity-25 transition ease-in-out duration-150">
                                    <i class="fas fa-save mr-2"></i> Guardar Orden
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center p-10 bg-yellow-50 rounded-lg border border-yellow-100">
                            <p class="text-yellow-700">Primero debe guardar una consulta para poder crear una orden de estudios.</p>
                        </div>
                    @endif

                    <!-- Estudios History -->
                    <div class="mt-10">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Historial de Estudios</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudios</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($historialEstudios as $estudio)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $estudio->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $estudio->consulta->doctor->name }} {{ $estudio->consulta->doctor->apellido_paterno }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($estudio->orden, 50) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end items-center space-x-2">
                                                    <!-- Edit Button -->
                                                    <a href="{{ route('consultas.estudios.edit', $estudio) }}" class="inline-flex items-center justify-center w-10 h-10 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors shadow-sm" title="Editar">
                                                        <i class="fas fa-edit text-xl"></i>
                                                    </a>

                                                    <!-- Print Button -->
                                                    <a href="{{ route('consultas.estudios.print', $estudio) }}" target="_blank" class="inline-flex items-center justify-center w-10 h-10 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition-colors shadow-sm" style="background-color: #1f2937;" title="Imprimir">
                                                        <i class="fas fa-print text-xl"></i>
                                                    </a>

                                                    <!-- View Files Button -->
                                                    @if($estudio->archivos && $estudio->archivos->count() > 0)
                                                    <div x-data="{ openFiles: false }">
                                                        <button @click="openFiles = !openFiles" type="button" class="inline-flex items-center justify-center w-10 h-10 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors shadow-sm" title="Ver Archivos">
                                                            <i class="fas fa-images text-xl"></i>
                                                        </button>
                                                        <!-- Files Popover -->
                                                        <div x-show="openFiles" @click.away="openFiles = false" class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-50 border border-gray-200 max-h-60 overflow-y-auto" style="display: none;">
                                                            <ul class="py-1">
                                                                @foreach($estudio->archivos as $archivo)
                                                                    <li>
                                                                        <a href="{{ Storage::url($archivo->path) }}" target="_blank" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 truncate">
                                                                            <i class="fas fa-file mr-2 text-gray-400"></i> {{ $archivo->nombre_original }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    <!-- Delete Button -->
                                                    <form action="{{ route('consultas.estudios.destroy', $estudio) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar esta orden de estudio?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center justify-center w-10 h-10 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="Borrar">
                                                            <i class="fas fa-trash text-xl"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay órdenes de estudios previas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function consultaHandler() {
            return {
                activeTab: 'consulta',
                peso: "{{ $paciente->peso ?? '' }}",
                estatura: "{{ $paciente->estatura ?? '' }}",
                alergias: "{{ $paciente->alergias ?? '' }}",
                selectedPlantillaId: '',
                campos: [],
                showHistory: false,
                
                // File Upload Logic
                isDragging: false,
                filesArray: [],

                handleDrop(e) {
                    this.isDragging = false;
                    const droppedFiles = e.dataTransfer.files;
                    this.addFiles(droppedFiles);
                },

                handleFiles(e) {
                    const files = e.target.files;
                    this.addFiles(files);
                },

                addFiles(files) {
                    for(let i=0; i<files.length; i++) {
                        if(files[i].size <= 5242880) { // 5MB
                            // Check for duplicates?
                            this.filesArray.push(files[i]);
                        } else {
                            alert('El archivo ' + files[i].name + ' excede el límite de 5MB');
                        }
                    }
                    this.updateFileInput();
                },

                removeFile(index) {
                    this.filesArray.splice(index, 1);
                    this.updateFileInput();
                },

                updateFileInput() {
                    const dt = new DataTransfer();
                    this.filesArray.forEach(file => dt.items.add(file));
                    this.$refs.fileInput.files = dt.files;
                },
                    
                async loadPlantillaCampos() {
                    if (!this.selectedPlantillaId) {
                        this.campos = [];
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/admin/plantillas/${this.selectedPlantillaId}/campos`);
                        const data = await response.json();
                        this.campos = data;
                    } catch (error) {
                        console.error('Error loading fields:', error);
                        this.campos = [];
                    }
                }
            }
        }
    </script>
</x-admin-layout>
