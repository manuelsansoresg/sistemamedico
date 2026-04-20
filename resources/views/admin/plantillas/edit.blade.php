<x-admin-layout>
    <div class="py-10">
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
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('plantillas.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">Plantillas</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Editar</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Editar Plantilla: {{ $plantilla->nombre }}</h2>
                        <a href="{{ route('plantillas.index') }}" class="text-gray-600 hover:text-[#0061F5] transition-colors">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                    </div>

                    <form action="{{ route('plantillas.update', $plantilla) }}" method="POST" x-data="templateForm()">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 gap-6 mb-6">
                            <!-- Nombre de la Plantilla -->
                            <div>
                                <label for="nombre" class="block text-sm font-bold text-gray-700">Nombre de la Plantilla</label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $plantilla->nombre) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>

                            <!-- Selector de Doctor (Solo Root) -->
                            @role('root')
                            <div>
                                <label for="user_id" class="block text-sm font-bold text-gray-700">Asignar a Doctor</label>
                                <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                    <option value="">Seleccione un doctor...</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ old('user_id', $plantilla->user_id) == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }} {{ $doctor->apellido_paterno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endrole
                        </div>

                        <!-- Sección de Campos Dinámicos -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">Campos de la Plantilla</h3>
                            
                            <div class="space-y-4">
                                <template x-for="(campo, index) in campos" :key="index">
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <div class="flex justify-end mb-2">
                                            <button type="button" @click="removeCampo(index)" class="text-red-500 hover:text-red-700" title="Eliminar campo" x-show="campos.length > 1">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Nombre del Campo -->
                                            <div>
                                                <label :for="'campo_nombre_' + index" class="block text-sm font-medium text-gray-700">Nombre del Campo</label>
                                                <input type="text" :name="'campos[' + index + '][nombre]'" :id="'campo_nombre_' + index" x-model="campo.nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required placeholder="Ej: Motivo de Consulta">
                                            </div>

                                            <!-- Tipo de Campo -->
                                            <div>
                                                <label :for="'campo_tipo_' + index" class="block text-sm font-medium text-gray-700">Tipo de Campo</label>
                                                <select :name="'campos[' + index + '][tipo]'" :id="'campo_tipo_' + index" x-model="campo.tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                                    <option value="text">Texto Corto</option>
                                                    <option value="textarea">Área de Texto</option>
                                                    <option value="date">Fecha</option>
                                                    <option value="select">Selección</option>
                                                </select>
                                            </div>

                                            <!-- Opciones (Solo para Select) -->
                                            <div class="col-span-full" x-show="campo.tipo === 'select'">
                                                <label :for="'campo_opciones_' + index" class="block text-sm font-medium text-gray-700">Opciones (separadas por coma)</label>
                                                <input type="text" :name="'campos[' + index + '][opciones]'" :id="'campo_opciones_' + index" x-model="campo.opciones" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" placeholder="Opción 1, Opción 2, Opción 3">
                                                <p class="text-xs text-gray-500 mt-1">Ingrese los valores separados por comas.</p>
                                            </div>

                                            <!-- Obligatorio -->
                                            <div class="flex items-center">
                                                <input type="checkbox" :name="'campos[' + index + '][es_obligatorio]'" :id="'campo_obligatorio_' + index" x-model="campo.es_obligatorio" value="1" class="h-4 w-4 text-[#0061F5] focus:ring-[#0061F5] border-gray-300 rounded">
                                                <label :for="'campo_obligatorio_' + index" class="ml-2 block text-sm text-gray-900">
                                                    Es obligatorio
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4">
                                <button type="button" @click="addCampo()" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-plus mr-2"></i> Agregar Campo
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0051CC] focus:bg-[#0051CC] active:bg-[#004499] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Actualizar Plantilla
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="templateCamposData" data-campos='@json($plantilla->campos)' class="hidden"></div>

    <script>
        function templateForm() {
            return {
                campos: [],
                init() {
                    const el = document.getElementById('templateCamposData');
                    const raw = el ? el.getAttribute('data-campos') : null;
                    let existingCampos = [];
                    try {
                        existingCampos = raw ? JSON.parse(raw) : [];
                    } catch (e) {
                        existingCampos = [];
                    }
                    if (existingCampos && existingCampos.length > 0) {
                        this.campos = existingCampos.map(c => ({
                            nombre: c.nombre,
                            tipo: c.tipo,
                            es_obligatorio: !!c.es_obligatorio,
                            opciones: Array.isArray(c.opciones) ? c.opciones.join(', ') : (c.opciones || '')
                        }));
                    } else {
                        this.addCampo();
                    }
                },
                addCampo() {
                    this.campos.push({
                        nombre: '',
                        tipo: 'text',
                        es_obligatorio: false,
                        opciones: ''
                    });
                },
                removeCampo(index) {
                    if (this.campos.length > 1) {
                        this.campos.splice(index, 1);
                    }
                }
            }
        }
    </script>
</x-admin-layout>
