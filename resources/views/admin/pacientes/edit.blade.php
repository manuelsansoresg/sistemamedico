<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('pacientes.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Pacientes</a>
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
                        <h2 class="text-xl font-bold text-gray-800">Editar Paciente: {{ $paciente->name }}</h2>
                        <a href="{{ route('pacientes.index') }}" class="text-gray-600 hover:text-blue-600 transition-colors">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </a>
                    </div>

                    <form action="{{ route('pacientes.update', $paciente) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <!-- Datos Personales -->
                            <div class="col-span-full">
                                <h3 class="text-lg font-semibold border-b pb-2 mb-4 text-gray-700">Datos Personales</h3>
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
                                <select id="sexo" name="sexo" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
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
                                <input id="activo" type="checkbox" name="activo" value="1" {{ old('activo', $paciente->activo) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 cursor-pointer w-5 h-5">
                                <label for="activo" class="ml-2 text-sm font-medium text-gray-900 cursor-pointer">Activo</label>
                            </div>

                            <div class="flex items-center">
                                <input id="perfil_compartido" type="checkbox" name="perfil_compartido" value="1" {{ old('perfil_compartido', $paciente->perfil_compartido) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 cursor-pointer w-5 h-5">
                                <label for="perfil_compartido" class="ml-2 text-sm font-medium text-gray-900 cursor-pointer">Perfil Compartido</label>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ml-4">
                                {{ __('Actualizar Paciente') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
