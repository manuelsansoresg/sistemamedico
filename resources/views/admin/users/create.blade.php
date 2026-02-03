<x-admin-layout>
    <div class="py-12">
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
                    <li class="inline-flex items-center">
                        <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            Usuarios
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Crear</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">Nuevo Usuario</h2>
                    
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">¡Ups!</strong>
                            <span class="block sm:inline">Por favor corrige los siguientes errores:</span>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        
                        <!-- Sección: Información Personal -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Información Personal</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-bold text-gray-700">Nombre</label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                </div>
                                <div>
                                    <label for="apellido_paterno" class="block text-sm font-bold text-gray-700">Apellido Paterno</label>
                                    <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="apellido_materno" class="block text-sm font-bold text-gray-700">Apellido Materno</label>
                                    <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="telefono" class="block text-sm font-bold text-gray-700">Teléfono</label>
                                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="cedula_profesional" class="block text-sm font-bold text-gray-700">Cédula Profesional</label>
                                    <input type="text" name="cedula_profesional" id="cedula_profesional" value="{{ old('cedula_profesional') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="especialidad_id" class="block text-sm font-bold text-gray-700">Especialidad</label>
                                    <select name="especialidad_id" id="especialidad_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach($especialidades as $especialidad)
                                            <option value="{{ $especialidad->id }}" {{ old('especialidad_id') == $especialidad->id ? 'selected' : '' }}>{{ $especialidad->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Información de Cuenta -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Información de Cuenta</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="email" class="block text-sm font-bold text-gray-700">Correo Electrónico</label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                </div>
                                <div>
                                    <label for="password" class="block text-sm font-bold text-gray-700">Contraseña</label>
                                    <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700">Confirmar Contraseña</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Rol y Asignaciones -->
                        <div class="mb-8" x-data="{ selectedRole: '{{ old('role') }}' }">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">Rol y Asignaciones</h3>
                            
                            <div class="mb-6">
                                <label for="role" class="block text-sm font-bold text-gray-700 mb-2">Rol del Usuario</label>
                                <div class="flex flex-wrap gap-4">
                                    @foreach($roles as $role)
                                        <div class="flex items-center">
                                            <input type="radio" x-model="selectedRole" name="role" id="role_{{ $role->id }}" value="{{ $role->name }}" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300" {{ old('role') == $role->name ? 'checked' : '' }} required>
                                            <label for="role_{{ $role->id }}" class="ml-2 block text-sm text-gray-700 uppercase">
                                                {{ $role->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Asignar Clínicas</label>
                                    <div class="bg-gray-50 p-4 rounded-md border border-gray-200 h-48 overflow-y-auto">
                                        @foreach($clinicas as $clinica)
                                            <div class="flex items-center mb-2">
                                                <input type="checkbox" name="clinicas[]" id="clinica_{{ $clinica->id }}" value="{{ $clinica->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500" {{ is_array(old('clinicas')) && in_array($clinica->id, old('clinicas')) ? 'checked' : '' }}>
                                                <label for="clinica_{{ $clinica->id }}" class="ml-2 text-sm text-gray-700">
                                                    {{ $clinica->nombre }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Selecciona las clínicas a las que este usuario tendrá acceso.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Asignar Consultorios</label>
                                    <div class="bg-gray-50 p-4 rounded-md border border-gray-200 h-48 overflow-y-auto">
                                        @foreach($consultorios as $consultorio)
                                            <div class="flex items-center mb-2">
                                                <input type="checkbox" name="consultorios[]" id="consultorio_{{ $consultorio->id }}" value="{{ $consultorio->id }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500" {{ is_array(old('consultorios')) && in_array($consultorio->id, old('consultorios')) ? 'checked' : '' }}>
                                                <label for="consultorio_{{ $consultorio->id }}" class="ml-2 text-sm text-gray-700">
                                                    {{ $consultorio->nombre }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Selecciona los consultorios a los que este usuario tendrá acceso.</p>
                                </div>
                            </div>

                            @if(auth()->user()->hasRole(['doctor', 'root']))
                            <div class="mb-6" x-show="['asistente', 'secretaria'].includes(selectedRole)" x-transition>
                                <label class="block text-sm font-bold text-gray-700 mb-2">Permisos de Descarga (Expedientes)</label>
                                <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($permissions as $permission)
                                            <div class="flex items-center">
                                                <input type="checkbox" name="permissions[]" id="perm_{{ $permission->id }}" value="{{ $permission->name }}" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500" {{ is_array(old('permissions')) && in_array($permission->name, old('permissions')) ? 'checked' : '' }}>
                                                <label for="perm_{{ $permission->id }}" class="ml-2 text-sm text-gray-700 capitalize">
                                                    {{ str_replace('descargar ', '', $permission->name) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Selecciona los permisos de descarga para este usuario.</p>
                            </div>
                            @endif
                        </div>

                        <div class="flex justify-end mt-6">
                            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors mr-2">Cancelar</a>
                            <x-primary-button>Guardar Usuario</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
