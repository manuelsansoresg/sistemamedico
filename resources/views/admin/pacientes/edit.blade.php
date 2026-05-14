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
                            <a href="{{ route('pacientes.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">Pacientes</a>
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
                        <h2 class="text-xl font-bold text-gray-800">Editar</h2>
                        <a href="{{ route('pacientes.index') }}" class="text-gray-600 hover:text-[#0061F5] transition-colors">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                    </div>

                    <form action="{{ route('pacientes.update', $paciente) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <!-- Datos Personales -->
                            <div class="col-span-full">
                                <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-gray-700">Datos Personales</h3>
                            </div>

                            <div class="md:col-span-3" x-data="{
                                previewUrl: '{{ $paciente->profile_photo_url }}',
                                fileName: '',
                                dragging: false,
                                setFile(file) {
                                    if (!file) return;
                                    this.fileName = file.name;
                                    this.previewUrl = URL.createObjectURL(file);
                                    const dt = new DataTransfer();
                                    dt.items.add(file);
                                    this.$refs.photoInput.files = dt.files;
                                },
                                onSelect(e) {
                                    const file = e && e.target && e.target.files ? e.target.files[0] : null;
                                    this.setFile(file);
                                },
                                onDrop(e) {
                                    this.dragging = false;
                                    const file = e && e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files[0] : null;
                                    this.setFile(file);
                                },
                            }">
                                <label class="block text-sm font-bold text-gray-700">Foto de perfil</label>
                                <div class="mt-2 flex items-start gap-6">
                                    <img :src="previewUrl" alt="Foto de perfil" class="h-16 w-16 rounded-full object-cover border border-gray-200">
                                    <div class="flex-1">
                                        <div class="rounded-lg border-2 border-dashed p-4 transition-colors"
                                            :class="dragging ? 'border-[#0061F5] bg-[#E6F0FF]' : 'border-gray-300 bg-white'"
                                            @dragover.prevent="dragging = true"
                                            @dragleave.prevent="dragging = false"
                                            @drop.prevent="onDrop($event)">
                                            <div class="flex flex-col gap-2">
                                                <p class="text-sm font-semibold text-gray-800">Arrastra y suelta la imagen aquí</p>
                                                <p class="text-xs text-gray-500">JPG, PNG o WEBP (máx. 2MB)</p>
                                                <div>
                                                    <button type="button" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors" @click="$refs.photoInput.click()">
                                                        Seleccionar archivo
                                                    </button>
                                                    <input type="file" x-ref="photoInput" name="profile_photo" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onSelect($event)">
                                                </div>
                                                <p class="text-xs text-gray-600" x-show="fileName" x-text="fileName"></p>
                                            </div>
                                        </div>
                                        <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <x-input-label for="name" :value="__('Nombre *')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $paciente->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="apellido_paterno" :value="__('Apellido Paterno')" />
                                <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno', $paciente->apellido_paterno)" />
                                <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="apellido_materno" :value="__('Apellido Materno')" />
                                <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno', $paciente->apellido_materno)" />
                                <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="curp" :value="__('CURP')" />
                                <x-text-input id="curp" class="block mt-1 w-full" type="text" name="curp" :value="old('curp', $paciente->curp)" maxlength="18" />
                                <x-input-error :messages="$errors->get('curp')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="fecha_nacimiento" :value="__('Fecha de Nacimiento')" />
                                <x-text-input id="fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento', $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->format('Y-m-d') : '')" />
                                <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="sexo" :value="__('Sexo')" />
                                <select id="sexo" name="sexo" class="block mt-1 w-full border-gray-300 focus:border-[#0061F5] focus:ring-[#0061F5] rounded-md shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="M" {{ old('sexo', $paciente->sexo) == 'M' ? 'selected' : '' }}>Masculino</option>
                                    <option value="F" {{ old('sexo', $paciente->sexo) == 'F' ? 'selected' : '' }}>Femenino</option>
                                </select>
                                <x-input-error :messages="$errors->get('sexo')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Datos de Contacto y Médicos -->
                            <div class="col-span-full">
                                <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-gray-700">Datos de Contacto y Médicos</h3>
                            </div>

                            <div>
                                <x-input-label for="telefono" :value="__('Teléfono')" />
                                <x-text-input id="telefono" class="block mt-1 w-full" type="text" name="telefono" :value="old('telefono', $paciente->telefono)" />
                                <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="numero_imss" :value="__('Número IMSS')" />
                                <x-text-input id="numero_imss" class="block mt-1 w-full" type="text" name="numero_imss" :value="old('numero_imss', $paciente->numero_imss)" />
                                <x-input-error :messages="$errors->get('numero_imss')" class="mt-2" />
                            </div>

                            <div class="col-span-full">
                                <x-input-label for="direccion" :value="__('Dirección')" />
                                <x-text-input id="direccion" class="block mt-1 w-full" type="text" name="direccion" :value="old('direccion', $paciente->direccion)" />
                                <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Cuenta de Usuario -->
                            <div class="col-span-full">
                                <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-gray-700">Cuenta de Usuario</h3>
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Correo Electrónico *')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $paciente->email)" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div></div> <!-- Spacer -->

                            <div>
                                <x-input-label for="password" :value="__('Contraseña (Dejar en blanco para mantener)')" />
                                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
                                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center space-x-6 mb-6 relative z-10">
                            <div class="flex items-center">
                                <input id="activo" type="checkbox" name="activo" value="1" {{ old('activo', $paciente->activo) ? 'checked' : '' }} class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] cursor-pointer w-5 h-5">
                                <label for="activo" class="ml-2 text-sm font-medium text-gray-900 cursor-pointer">Activo</label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4 gap-2">
                            <a href="{{ route('pacientes.index') }}" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors">{{ __('common.buttons.cancel') }}</a>
                            <x-primary-button>
                                {{ __('Actualizar Paciente') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
