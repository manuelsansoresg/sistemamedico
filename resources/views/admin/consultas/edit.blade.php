<x-admin-layout>
    <div class="py-12" x-data="consultaEditHandler()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Breadcrumbs -->
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
                            <a href="{{ route('consultas.create', $consulta->cita_id) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">{{ __('consultas.tabs.consultation') }}</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('common.buttons.edit') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Patient Header Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ __('consultas.messages.editing_consultation_for', ['patient' => $consulta->paciente->name.' '.$consulta->paciente->apellido_paterno]) }}
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ __('consultas.messages.original_date', ['date' => $consulta->created_at->format('d/m/Y H:i')]) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('consultas.update', $consulta) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Health Metrics -->
                    <div class="bg-[#E6F0FF] p-6 rounded-lg border border-[#CCE0FF] mb-6">
                        <h3 class="text-lg font-semibold text-[#004499] mb-4">
                            <i class="fas fa-heartbeat mr-2"></i> {{ __('consultas.sections.vital_signs_and_allergies') }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('consultas.fields.weight') }}</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="number" step="0.01" name="peso" value="{{ old('peso', $consulta->peso) }}" class="focus:ring-[#0061F5] focus:border-[#0061F5] flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300" placeholder="0.00">
                                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">kg</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('consultas.fields.height') }}</label>
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    <input type="number" step="0.01" name="estatura" value="{{ old('estatura', $consulta->estatura) }}" class="focus:ring-[#0061F5] focus:border-[#0061F5] flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300" placeholder="0.00">
                                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">m</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('consultas.fields.allergies') }}</label>
                                <textarea name="alergias" rows="1" class="shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md">{{ old('alergias', $consulta->alergias) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('consultas.fields.template') }}</label>
                        <select name="plantilla_id" x-model="selectedPlantillaId" @change="loadPlantillaCampos()" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                            @foreach($plantillas as $plantilla)
                                <option value="{{ $plantilla->id }}" {{ $consulta->plantilla_id == $plantilla->id ? 'selected' : '' }}>{{ $plantilla->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dynamic Fields Area -->
                    <div class="space-y-4 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200 min-h-[200px]" id="campos-container">
                        <template x-for="campo in campos" :key="campo.id">
                            <div>
                                <label class="block text-sm font-medium text-gray-700" x-text="campo.etiqueta"></label>
                                
                                <!-- Text Input -->
                                <template x-if="campo.tipo === 'text'">
                                    <input type="text" :name="'valores[' + campo.id + ']'" :value="getValor(campo.id)" class="mt-1 focus:ring-[#0061F5] focus:border-[#0061F5] block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </template>
                                
                                <!-- Textarea -->
                                <template x-if="campo.tipo === 'textarea'">
                                    <textarea :name="'valores[' + campo.id + ']'" rows="3" class="mt-1 shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md" x-text="getValor(campo.id)"></textarea>
                                </template>
                                
                                <!-- Number -->
                                <template x-if="campo.tipo === 'number'">
                                    <input type="number" :name="'valores[' + campo.id + ']'" :value="getValor(campo.id)" class="mt-1 focus:ring-[#0061F5] focus:border-[#0061F5] block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </template>
                                
                                <!-- Select -->
                                <template x-if="campo.tipo === 'select'">
                                    <select :name="'valores[' + campo.id + ']'" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                                        <template x-for="opcion in campo.opciones.split(',')" :key="opcion">
                                            <option :value="opcion.trim()" x-text="opcion.trim()" :selected="getValor(campo.id) == opcion.trim()"></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('consultas.create', $consulta->cita_id) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                            {{ __('common.buttons.cancel') }}
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:border-yellow-700 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <i class="fas fa-save mr-2"></i> {{ __('common.buttons.update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function consultaEditHandler() {
            return {
                selectedPlantillaId: "{{ $consulta->plantilla_id }}",
                campos: [],
                valores: @json($consulta->valores->pluck('valor', 'plantilla_campo_id')),

                init() {
                    this.loadPlantillaCampos();
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
                },

                getValor(campoId) {
                    return this.valores[campoId] || '';
                }
            }
        }
    </script>
</x-admin-layout>
