<x-admin-layout>
    <div class="py-10">
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
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('plantillas.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">{{ __('plantillas.title') }}</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('common.breadcrumbs.create') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">{{ __('plantillas.form.new_title') }}</h2>
                        <a href="{{ route('plantillas.index') }}" class="text-gray-600 hover:text-[#0061F5] transition-colors">
                            <i class="fas fa-arrow-left mr-1"></i> {{ __('common.back_to_list') }}
                        </a>
                    </div>

                    <form action="{{ route('plantillas.store') }}" method="POST" x-data="templateForm()">
                        @csrf
                        
                        <div class="grid grid-cols-1 gap-6 mb-6">
                            <!-- Nombre de la Plantilla -->
                            <div>
                                <label for="nombre" class="block text-sm font-bold text-gray-700">{{ __('plantillas.form.template_name') }}</label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                            </div>

                            <!-- Selector de Doctor (Solo Root) -->
                            @role('root')
                            <div>
                                <label for="user_id" class="block text-sm font-bold text-gray-700">{{ __('plantillas.form.assign_doctor') }}</label>
                                <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                    <option value="">{{ __('plantillas.form.select_doctor_placeholder') }}</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ old('user_id') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }} {{ $doctor->apellido_paterno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endrole
                        </div>

                        <!-- Sección de Campos Dinámicos -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('plantillas.form.fields_title') }}</h3>
                            
                            <div class="space-y-4">
                                <template x-for="(campo, index) in campos" :key="index">
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <div class="flex justify-end mb-2">
                                            <button type="button" @click="removeCampo(index)" class="text-red-500 hover:text-red-700" title="{{ __('plantillas.form.field_delete') }}" x-show="campos.length > 1">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <!-- Nombre del Campo -->
                                            <div>
                                                <label :for="'campo_nombre_' + index" class="block text-sm font-medium text-gray-700">{{ __('plantillas.form.field_name') }}</label>
                                                <input type="text" :name="'campos[' + index + '][nombre]'" :id="'campo_nombre_' + index" x-model="campo.nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required placeholder="{{ __('plantillas.form.field_name_placeholder') }}">
                                            </div>

                                            <!-- Tipo de Campo -->
                                            <div>
                                                <label :for="'campo_tipo_' + index" class="block text-sm font-medium text-gray-700">{{ __('plantillas.form.field_type') }}</label>
                                                <select :name="'campos[' + index + '][tipo]'" :id="'campo_tipo_' + index" x-model="campo.tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                                    <option value="text">{{ __('plantillas.form.types.text') }}</option>
                                                    <option value="textarea">{{ __('plantillas.form.types.textarea') }}</option>
                                                    <option value="date">{{ __('plantillas.form.types.date') }}</option>
                                                    <option value="select">{{ __('plantillas.form.types.select') }}</option>
                                                </select>
                                            </div>

                                            <!-- Opciones (Solo para Select) -->
                                            <div class="col-span-full" x-show="campo.tipo === 'select'">
                                                <label :for="'campo_opciones_' + index" class="block text-sm font-medium text-gray-700">{{ __('plantillas.form.options_label') }}</label>
                                                <input type="text" :name="'campos[' + index + '][opciones]'" :id="'campo_opciones_' + index" x-model="campo.opciones" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" placeholder="{{ __('plantillas.form.options_placeholder') }}">
                                                <p class="text-xs text-gray-500 mt-1">{{ __('plantillas.form.options_help') }}</p>
                                            </div>

                                            <!-- Obligatorio -->
                                            <div class="flex items-center">
                                                <input type="checkbox" :name="'campos[' + index + '][es_obligatorio]'" :id="'campo_obligatorio_' + index" x-model="campo.es_obligatorio" value="1" class="h-4 w-4 text-[#0061F5] focus:ring-[#0061F5] border-gray-300 rounded">
                                                <label :for="'campo_obligatorio_' + index" class="ml-2 block text-sm text-gray-900">
                                                    {{ __('plantillas.form.required') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4">
                                <button type="button" @click="addCampo()" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-plus mr-2"></i> {{ __('plantillas.form.add_field') }}
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-2">
                            <a href="{{ route('plantillas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                                {{ __('common.buttons.cancel') }}
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0051CC] focus:bg-[#0051CC] active:bg-[#004499] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('plantillas.form.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function templateForm() {
            return {
                campos: [
                    {
                        nombre: '',
                        tipo: 'text',
                        es_obligatorio: false,
                        opciones: ''
                    }
                ],
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
