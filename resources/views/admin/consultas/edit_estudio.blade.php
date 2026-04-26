<x-admin-layout>
    <div class="py-12" x-data="estudioEditHandler()">
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
                            <a href="{{ route('consultas.create', $estudio->consulta->cita_id) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">Consulta</a>
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

            <!-- Header Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-[#0061F5]">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            Editando Estudio de: {{ $estudio->consulta->paciente->name }} {{ $estudio->consulta->paciente->apellido_paterno }}
                        </h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Fecha original: {{ $estudio->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('consultas.estudios.update.post', $estudio) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Orden de Estudio</label>
                        <textarea name="orden" rows="4" class="mt-1 shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Escriba los estudios requeridos...">{{ old('orden', $estudio->orden) }}</textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                        <textarea name="observacion" rows="2" class="mt-1 shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Observaciones adicionales...">{{ old('observacion', $estudio->observacion) }}</textarea>
                    </div>

                    <!-- Existing Files -->
                    @if($estudio->archivos->count() > 0)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Archivos Existentes</label>
                        <div class="bg-gray-50 rounded-md border border-gray-200 p-4 space-y-2">
                            @foreach($estudio->archivos as $archivo)
                                <div class="flex items-center justify-between p-2 bg-white rounded-md border border-gray-100 shadow-sm">
                                    <div class="flex items-center space-x-3 truncate">
                                        <i class="fas fa-file-alt text-gray-400"></i>
                                        <a href="{{ asset($archivo->path) }}" target="_blank" class="text-sm text-[#0061F5] hover:text-[#004499] hover:underline truncate">
                                            {{ $archivo->nombre_original }}
                                        </a>
                                        <span class="text-xs text-gray-400">({{ number_format($archivo->size / 1024, 2) }} KB)</span>
                                    </div>
                                    <a href="{{ route('consultas.estudios.archivos.delete', $archivo) }}"
                                       onclick="return confirm('¿Deseas eliminar este archivo?');"
                                       class="inline-flex cursor-pointer items-center justify-center w-9 h-9 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm"
                                       title="Eliminar archivo">
                                        <i class="fas fa-trash text-sm"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Haz clic en el ícono de eliminar para borrar el archivo.</p>
                    </div>
                    @endif
                    
                    <!-- New Files Upload -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Adjuntar Nuevos Archivos</label>
                        
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
                                        <span>Subir</span>
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

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('consultas.create', $estudio->consulta->cita_id) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                            Cancelar
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0051CC] active:bg-[#004499] focus:outline-none focus:border-[#004499] focus:ring ring-[#80B0FA] disabled:opacity-25 transition ease-in-out duration-150">
                            <i class="fas fa-save mr-2"></i> Actualizar Estudio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function estudioEditHandler() {
            return {
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
                }
            }
        }
    </script>
</x-admin-layout>
