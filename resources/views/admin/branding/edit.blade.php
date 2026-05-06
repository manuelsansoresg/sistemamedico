<x-admin-layout>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('branding.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-6 mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ __('branding.title') }}</h2>
                            <p class="text-sm text-gray-500">{{ __('branding.description') }}</p>
                        </div>
                    </div>

                    <form action="{{ route('branding.update') }}" method="POST" enctype="multipart/form-data"
                        x-data="{
                            previewUrl: '{{ $user->profile_photo_path ? asset('storage/'.$user->profile_photo_path) : '' }}',
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
                        @csrf

                        <!-- Foto de perfil -->
                        <div class="mb-8">
                            <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('branding.fields.profile_photo') }}</label>
                            <div class="flex items-start gap-6">
                                <div class="h-24 w-24 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden border-2 border-gray-200">
                                    <img x-show="previewUrl" :src="previewUrl" alt="Foto" class="h-full w-full object-cover" style="display: none;">
                                    <i x-show="!previewUrl" class="fas fa-user text-gray-400 text-3xl"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="rounded-lg border-2 border-dashed p-4 transition-colors"
                                        :class="dragging ? 'border-[#0061F5] bg-[#E6F0FF]' : 'border-gray-300 bg-white'"
                                        @dragover.prevent="dragging = true"
                                        @dragleave.prevent="dragging = false"
                                        @drop.prevent="onDrop($event)">
                                        <div class="flex flex-col gap-2">
                                            <p class="text-sm font-semibold text-gray-800">{{ __('common.drag_drop_image') }}</p>
                                            <p class="text-xs text-gray-500">JPG, PNG, WEBP (máx 2MB)</p>
                                            <div>
                                                <button type="button" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors" @click="$refs.photoInput.click()">
                                                    {{ __('common.buttons.select_file') }}
                                                </button>
                                                <input type="file" name="profile_photo" x-ref="photoInput" class="hidden" accept="image/jpeg,image/png,image/webp" @change="onSelect($event)">
                                            </div>
                                            <p class="text-xs text-gray-600" x-show="fileName" x-text="fileName"></p>
                                        </div>
                                    </div>
                                    @error('profile_photo')
                                        <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Datos generales -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-bold text-gray-700">{{ __('users.fields.name') }}</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-bold text-gray-700">{{ __('users.fields.email') }}</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="apellido_paterno" class="block text-sm font-bold text-gray-700">{{ __('users.fields.apellido_paterno') }}</label>
                                <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno', $user->apellido_paterno) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                            </div>

                            <div>
                                <label for="apellido_materno" class="block text-sm font-bold text-gray-700">{{ __('users.fields.apellido_materno') }}</label>
                                <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno', $user->apellido_materno) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                            </div>

                            <div>
                                <label for="telefono" class="block text-sm font-bold text-gray-700">{{ __('users.fields.phone') }}</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $user->telefono) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                            </div>

                            @if($isDoctor)
                                <div>
                                    <label for="cedula_profesional" class="block text-sm font-bold text-gray-700">{{ __('users.fields.professional_id') }}</label>
                                    <input type="text" name="cedula_profesional" id="cedula_profesional" value="{{ old('cedula_profesional', $user->cedula_profesional) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>

                                <div>
                                    <label for="especialidad_id" class="block text-sm font-bold text-gray-700">{{ __('users.fields.specialty') }}</label>
                                    <select name="especialidad_id" id="especialidad_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                        <option value="">{{ __('common.select') }}</option>
                                        @foreach($especialidades as $esp)
                                            <option value="{{ $esp->id }}" {{ old('especialidad_id', $user->especialidad_id) == $esp->id ? 'selected' : '' }}>{{ $esp->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="curp" class="block text-sm font-bold text-gray-700">CURP</label>
                                    <input type="text" name="curp" id="curp" value="{{ old('curp', $user->curp) }}" maxlength="18" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>

                                <div>
                                    <label for="fecha_nacimiento" class="block text-sm font-bold text-gray-700">{{ __('users.fields.birth_date') }}</label>
                                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" value="{{ old('fecha_nacimiento', $user->fecha_nacimiento?->format('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>

                                <div>
                                    <label for="sexo" class="block text-sm font-bold text-gray-700">{{ __('users.fields.gender') }}</label>
                                    <select name="sexo" id="sexo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                        <option value="">{{ __('common.select') }}</option>
                                        <option value="M" {{ old('sexo', $user->sexo) == 'M' ? 'selected' : '' }}>Masculino</option>
                                        <option value="F" {{ old('sexo', $user->sexo) == 'F' ? 'selected' : '' }}>Femenino</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="numero_imss" class="block text-sm font-bold text-gray-700">{{ __('users.fields.imss_number') }}</label>
                                    <input type="text" name="numero_imss" id="numero_imss" value="{{ old('numero_imss', $user->numero_imss) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="direccion" class="block text-sm font-bold text-gray-700">{{ __('users.fields.address') }}</label>
                                    <textarea name="direccion" id="direccion" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">{{ old('direccion', $user->direccion) }}</textarea>
                                </div>

                                <div>
                                    <label for="peso" class="block text-sm font-bold text-gray-700">{{ __('users.fields.weight') }} (kg)</label>
                                    <input type="number" name="peso" id="peso" value="{{ old('peso', $user->peso) }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>

                                <div>
                                    <label for="estatura" class="block text-sm font-bold text-gray-700">{{ __('users.fields.height') }} (cm)</label>
                                    <input type="number" name="estatura" id="estatura" value="{{ old('estatura', $user->estatura) }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>

                                <div class="md:col-span-2">
                                    <label for="alergias" class="block text-sm font-bold text-gray-700">{{ __('users.fields.allergies') }}</label>
                                    <textarea name="alergias" id="alergias" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">{{ old('alergias', $user->alergias) }}</textarea>
                                </div>
                            @endif
                        </div>

                        <!-- Cambio de contraseña -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">{{ __('branding.change_password') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="password" class="block text-sm font-bold text-gray-700">{{ __('common.new_password') }}</label>
                                    <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                    <p class="text-xs text-gray-500 mt-1">{{ __('branding.password_hint') }}</p>
                                    @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-bold text-gray-700">{{ __('common.confirm_password') }}</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-8">
                            <button type="submit" class="px-6 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors">
                                {{ __('common.buttons.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
