<x-admin-layout>
    <div class="py-12">
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
                    <li class="inline-flex items-center">
                        <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            {{ __('users.breadcrumbs.index') }}
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('users.breadcrumbs.edit') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">{{ __('users.breadcrumbs.edit') }}</h2>
                    
                    <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Sección: Información Personal -->
                        <div class="mb-8" id="foto">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">{{ __('users.sections.personal_info') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="md:col-span-3" x-data="{
                                    previewUrl: '{{ $user->profile_photo_url }}',
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
                                    <label class="block text-sm font-bold text-gray-700">{{ __('users.fields.photo') }}</label>
                                    <div class="mt-2 flex items-start gap-6">
                                        <img :src="previewUrl" alt="{{ __('users.fields.photo') }}" class="h-16 w-16 rounded-full object-cover border border-gray-200">
                                        <div class="flex-1">
                                            <div class="rounded-lg border-2 border-dashed p-4 transition-colors"
                                                :class="dragging ? 'border-[#0061F5] bg-[#E6F0FF]' : 'border-gray-300 bg-white'"
                                                @dragover.prevent="dragging = true"
                                                @dragleave.prevent="dragging = false"
                                                @drop.prevent="onDrop($event)">
                                                <div class="flex flex-col gap-2">
                                                    <p class="text-sm font-semibold text-gray-800">{{ __('common.drag_drop_image') }}</p>
                                                    <p class="text-xs text-gray-500">{{ __('common.upload_image_hint') }}</p>
                                                    <div>
                                                        <button type="button" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors" @click="$refs.photoInput.click()">
                                                            {{ __('common.select_file') }}
                                                        </button>
                                                        <input type="file" x-ref="photoInput" name="profile_photo" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onSelect($event)">
                                                    </div>
                                                    <p class="text-xs text-gray-600" x-show="fileName" x-text="fileName"></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="name" class="block text-sm font-bold text-gray-700">{{ __('users.fields.name') }}</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                </div>
                                <div>
                                    <label for="apellido_paterno" class="block text-sm font-bold text-gray-700">{{ __('users.fields.last_name') }}</label>
                                    <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno', $user->apellido_paterno) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>
                                <div>
                                    <label for="apellido_materno" class="block text-sm font-bold text-gray-700">{{ __('users.fields.mother_last_name') }}</label>
                                    <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno', $user->apellido_materno) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>
                                <div>
                                    <label for="telefono" class="block text-sm font-bold text-gray-700">{{ __('users.fields.phone') }}</label>
                                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $user->telefono) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>
                                <div>
                                    <label for="cedula_profesional" class="block text-sm font-bold text-gray-700">{{ __('users.fields.professional_id') }}</label>
                                    <input type="text" name="cedula_profesional" id="cedula_profesional" value="{{ old('cedula_profesional', $user->cedula_profesional) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>
                                <div>
                                    <label for="especialidad_id" class="block text-sm font-bold text-gray-700">{{ __('users.fields.specialty') }}</label>
                                    <select name="especialidad_id" id="especialidad_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                        <option value="">{{ __('common.select_option') }}</option>
                                        @foreach($especialidades as $especialidad)
                                            <option value="{{ $especialidad->id }}" {{ old('especialidad_id', $user->especialidad_id) == $especialidad->id ? 'selected' : '' }}>{{ $especialidad->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Sección: Información de Cuenta -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">{{ __('users.sections.account_info') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="email" class="block text-sm font-bold text-gray-700">{{ __('users.fields.email') }}</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                </div>
                                <div>
                                    <label for="password" class="block text-sm font-bold text-gray-700">{{ __('users.fields.password') }} ({{ __('users.help.keep_password') }})</label>
                                    <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700">{{ __('users.fields.password_confirmation') }}</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>
                            </div>
                        </div>

                        @php
                            $isDoctorSelf = auth()->user()->hasRole('doctor') && auth()->id() === $user->id;
                        @endphp

                        <!-- Sección: Rol y Asignaciones -->
                        <div class="mb-8" x-data="{ selectedRole: '{{ old('role', $user->roles->first()?->name) }}' }">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">{{ __('users.sections.role_assignments') }}</h3>
                            
                            <div class="mb-6">
                                <label for="role" class="block text-sm font-bold text-gray-700 mb-2">{{ __('users.fields.user_role') }}</label>
                                @if(auth()->user()->hasRole('doctor') && auth()->id() === $user->id)
                                    <div class="p-2 bg-gray-100 rounded border border-gray-300">
                                        <span class="text-gray-700 uppercase font-medium">{{ $user->roles->first()?->name }}</span>
                                        <p class="text-xs text-gray-500 mt-1">{{ __('users.help.cannot_edit_own_role') }}</p>
                                    </div>
                                @else
                                    <div class="flex flex-wrap gap-4">
                                        @foreach($roles as $role)
                                            <div class="flex items-center">
                                                <input type="radio" x-model="selectedRole" name="role" id="role_{{ $role->id }}" value="{{ $role->name }}" class="focus:ring-[#0061F5] h-4 w-4 text-[#0061F5] border-gray-300" {{ old('role', $user->roles->first()?->name) == $role->name ? 'checked' : '' }} required>
                                                <label for="role_{{ $role->id }}" class="ml-2 block text-sm text-gray-700 uppercase">
                                                    {{ $role->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('users.fields.clinics') }}</label>
                                    <div class="bg-gray-50 p-4 rounded-md border border-gray-200 h-48 overflow-y-auto">
                                        @foreach($clinicas as $clinica)
                                            <div class="flex items-center mb-2">
                                                <input type="checkbox" name="clinicas[]" id="clinica_{{ $clinica->id }}" value="{{ $clinica->id }}" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" {{ (is_array(old('clinicas')) && in_array($clinica->id, old('clinicas'))) || (empty(old('clinicas')) && $user->clinicas->contains($clinica->id)) ? 'checked' : '' }} @if($isDoctorSelf) disabled @endif>
                                                <label for="clinica_{{ $clinica->id }}" class="ml-2 text-sm text-gray-700">
                                                    {{ $clinica->nombre }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('users.help.select_clinics_access') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('users.fields.consultorios') }}</label>
                                    <div class="bg-gray-50 p-4 rounded-md border border-gray-200 h-48 overflow-y-auto">
                                        @foreach($consultorios as $consultorio)
                                            <div class="flex items-center mb-2">
                                                <input type="checkbox" name="consultorios[]" id="consultorio_{{ $consultorio->id }}" value="{{ $consultorio->id }}" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" {{ (is_array(old('consultorios')) && in_array($consultorio->id, old('consultorios'))) || (empty(old('consultorios')) && $user->consultorios->contains($consultorio->id)) ? 'checked' : '' }} @if($isDoctorSelf) disabled @endif>
                                                <label for="consultorio_{{ $consultorio->id }}" class="ml-2 text-sm text-gray-700">
                                                    {{ $consultorio->nombre }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('users.help.select_offices_access') }}</p>
                                </div>
                            </div>

                            @if(auth()->user()->hasRole(['doctor', 'root']))
                            <div class="mt-6" x-show="['asistente', 'secretaria'].includes(selectedRole)" x-transition>
                                <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('users.help.download_permissions') }}</label>
                                <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($permissions as $permission)
                                        <div class="flex items-center">
                                            <input type="checkbox" name="permissions[]" id="perm_{{ $permission->id }}" value="{{ $permission->name }}" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" {{ (is_array(old('permissions')) && in_array($permission->name, old('permissions'))) || (empty(old('permissions')) && $user->hasPermissionTo($permission->name)) ? 'checked' : '' }}>
                                            <label for="perm_{{ $permission->id }}" class="ml-2 text-sm text-gray-700 capitalize">
                                                {{ str_replace('descargar ', '', $permission->name) }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="flex justify-end mt-6">
                            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors mr-2">{{ __('common.buttons.cancel') }}</a>
                            <button type="submit" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors">{{ __('common.buttons.update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
