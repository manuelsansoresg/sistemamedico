<x-guest-layout>
    <x-slot name="maxWidth">sm:max-w-5xl</x-slot>
    <x-slot name="header">
        <div class="bg-gradient-to-r from-[#002F53] to-[#004E8B] p-8 text-center">
            <div class="flex justify-center mb-4">
                <i class="fas fa-user-md text-white text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Registro de Doctores</h1>
            <p class="text-[#E1EFF9] text-lg">Complete los siguientes pasos para registrarse en nuestra plataforma médica</p>
        </div>
    </x-slot>

    <div x-data="registerWizard()" x-init="init()" class="p-4">
        
        <!-- Progress Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-between relative">
                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 -z-10"></div>
                <template x-for="i in 6">
                    <div class="relative z-10 flex flex-col items-center">
                        <div :class="{'bg-[#003E6F] text-white': step >= i, 'bg-gray-200 text-gray-500': step < i}" 
                             class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm transition-colors duration-300">
                            <span x-text="i"></span>
                        </div>
                        <div class="text-xs mt-1 text-gray-500 font-medium hidden sm:block" x-text="getStepTitle(i)"></div>
                    </div>
                </template>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" x-ref="registerForm" id="registerForm">
            @csrf
            
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Hay problemas con su registro:</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Hidden Inputs for State -->
            <input type="hidden" name="current_step" :value="step">
            <input type="hidden" name="tipo_registro" :value="tipo_registro">
            <input type="hidden" name="tipo_establecimiento" :value="tipo_establecimiento">
            <input type="hidden" name="paquete_id" :value="paquete_id">
            <input type="hidden" name="payment_method" :value="payment_method">

            <!-- Step 1: Tipo de Registro -->
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <h2 class="text-2xl font-bold text-center mb-6">Seleccione su tipo de registro</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Doctor -->
                    <div @click="tipo_registro = 'doctor'" 
                         :class="{'border-[#004E8B] bg-[#F2F8FD] ring-2 ring-[#004E8B]': tipo_registro === 'doctor', 'border-gray-200 hover:border-[#004E8B]/50': tipo_registro !== 'doctor'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="w-16 h-16 bg-[#E1EFF9] rounded-full flex items-center justify-center mb-4 text-[#004E8B]">
                            <i class="fas fa-user-md text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Doctor</h3>
                        <p class="text-gray-500 text-sm">Para médicos con cédula profesional que requieren gestión completa de pacientes y consultas.</p>
                    </div>

                    <!-- Otro Profesional -->
                    <div @click="tipo_registro = 'otro'" 
                         :class="{'border-[#004E8B] bg-[#F2F8FD] ring-2 ring-[#004E8B]': tipo_registro === 'otro', 'border-gray-200 hover:border-[#004E8B]/50': tipo_registro !== 'otro'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4 text-green-600">
                            <i class="fas fa-user-nurse text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Otro Profesional</h3>
                        <p class="text-gray-500 text-sm">Para otros profesionales de la salud (enfermeros, terapeutas, etc.) sin requerimiento de cédula médica.</p>
                    </div>
                </div>
            </div>

            <!-- Step 2: Tipo de Establecimiento -->
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
                <h2 class="text-2xl font-bold text-center mb-6">Seleccione su tipo de establecimiento</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Clinica -->
                    <div @click="tipo_establecimiento = 'clinica'" 
                         :class="{'border-[#004E8B] bg-[#F2F8FD] ring-2 ring-[#004E8B]': tipo_establecimiento === 'clinica', 'border-gray-200 hover:border-[#004E8B]/50': tipo_establecimiento !== 'clinica'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4 text-purple-600">
                            <i class="fas fa-hospital text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Clínica</h3>
                        <p class="text-gray-500 text-sm">Para establecimientos médicos con múltiples especialidades y personal.</p>
                    </div>

                    <!-- Consultorio -->
                    <div @click="tipo_establecimiento = 'consultorio'" 
                         :class="{'border-[#004E8B] bg-[#F2F8FD] ring-2 ring-[#004E8B]': tipo_establecimiento === 'consultorio', 'border-gray-200 hover:border-[#004E8B]/50': tipo_establecimiento !== 'consultorio'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mb-4 text-orange-600">
                            <i class="fas fa-clinic-medical text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2">Consultorio</h3>
                        <p class="text-gray-500 text-sm">Para consultorios médicos individuales o práctica privada.</p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Paquetes -->
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
                <h2 class="text-2xl font-bold text-center mb-6">Seleccione un Paquete</h2>
                
                <div x-show="filteredPaquetes.length === 0" class="text-center py-10 text-gray-500">
                    <p>No hay paquetes disponibles para la combinación seleccionada.</p>
                    <button type="button" @click="step = 1" class="text-[#004E8B] hover:underline mt-2">Volver al inicio</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <template x-for="paquete in filteredPaquetes" :key="paquete.id">
                        <div @click="selectPaquete(paquete)" 
                             :class="{'border-[#004E8B] bg-[#F2F8FD] ring-2 ring-[#004E8B] shadow-lg': paquete_id === paquete.id, 'border-gray-200 hover:border-[#004E8B]/50 shadow-sm': paquete_id !== paquete.id}"
                             class="cursor-pointer border rounded-xl p-6 flex flex-col relative transition-all duration-200 bg-white">
                            
                            <div x-show="paquete_id === paquete.id" class="absolute top-0 right-0 -mt-2 -mr-2 bg-[#004E8B] text-white rounded-full p-1 shadow">
                                <i class="fas fa-check"></i>
                            </div>

                            <h3 class="text-xl font-bold mb-2 text-gray-900" x-text="paquete.nombre"></h3>
                            <div class="text-3xl font-bold text-[#004E8B] mb-4">
                                $<span x-text="parseFloat(paquete.precio).toFixed(2)"></span>
                                <span class="text-sm text-gray-500 font-normal">/anual</span>
                            </div>
                            
                            <div class="border-t border-gray-100 my-4"></div>
                            
                            <ul class="text-sm text-gray-600 space-y-2 mb-6 flex-grow">
                                <!-- Assuming description can be split or just shown as text -->
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                                    <span x-text="paquete.tipo === 'clinica' ? 'Funciones para Clínica' : 'Funciones para Consultorio'"></span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-500 mt-1 mr-2"></i>
                                    <span x-text="paquete.validar_cedula ? 'Requiere Cédula Profesional' : 'No requiere Cédula'"></span>
                                </li>
                            </ul>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Step 4: Información Personal -->
            <div x-show="step === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
                <h2 class="text-2xl font-bold text-center mb-6">Información Personal</h2>
                
                <p class="text-sm text-gray-500 mb-4 text-right">
                    <span class="text-red-500">*</span> Campos obligatorios
                </p>

                <!-- Resumen Paquete Selection -->
                <div class="bg-[#F2F8FD] border border-[#E1EFF9] rounded-lg p-4 mb-6 flex justify-between items-center" x-show="selected_paquete">
                    <div>
                        <p class="text-sm text-[#002F53] font-medium">Paquete Seleccionado:</p>
                        <p class="font-bold text-[#001F37]" x-text="selected_paquete ? selected_paquete.nombre : ''"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-[#002F53] font-medium">Costo:</p>
                        <p class="font-bold text-[#001F37]">$<span x-text="selected_paquete ? parseFloat(selected_paquete.precio).toFixed(2) : '0.00'"></span></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div>
                        <x-input-label for="name">
                            {{ __('Nombre') }} <span class="text-red-500">*</span>
                        </x-input-label>
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <!-- Apellido Paterno -->
                    <div>
                        <x-input-label for="apellido_paterno">
                            {{ __('Apellido Paterno') }} <span class="text-red-500">*</span>
                        </x-input-label>
                        <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno')" required />
                        <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
                    </div>

                    <!-- Apellido Materno -->
                    <div>
                        <x-input-label for="apellido_materno" :value="__('Apellido Materno')" />
                        <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno')" />
                        <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div>
                        <x-input-label for="email">
                            {{ __('Email') }} <span class="text-red-500">*</span>
                        </x-input-label>
                        <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Telefono -->
                    <div>
                        <x-input-label for="telefono" :value="__('Teléfono')" />
                        <x-text-input id="telefono" class="block mt-1 w-full" type="text" name="telefono" :value="old('telefono')" />
                        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                    </div>
                </div>
                
                <!-- Doctor Specific Fields -->
                <div x-show="tipo_registro === 'doctor'" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-gray-200 pt-4">
                    <div>
                        <x-input-label for="cedula_profesional">
                             {{ __('Cédula Profesional') }} <span class="text-red-500">*</span>
                        </x-input-label>
                        <x-text-input id="cedula_profesional" class="block mt-1 w-full" type="text" name="cedula_profesional" :value="old('cedula_profesional')" />
                        <x-input-error :messages="$errors->get('cedula_profesional')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="especialidad_id">
                            {{ __('Especialidad') }} <span class="text-red-500">*</span>
                        </x-input-label>
                        <select id="especialidad_id" name="especialidad_id" class="block mt-1 w-full border-gray-300 focus:border-[#004E8B] focus:ring-[#004E8B] rounded-md shadow-sm">
                            <option value="">Seleccione una especialidad</option>
                            @foreach($especialidades as $especialidad)
                                <option value="{{ $especialidad->id }}" {{ old('especialidad_id') == $especialidad->id ? 'selected' : '' }}>{{ $especialidad->nombre }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('especialidad_id')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 border-t border-gray-200 pt-4">
                    <!-- Password -->
                    <div>
                        <x-input-label for="password">
                            {{ __('Contraseña') }} <span class="text-red-500">*</span>
                        </x-input-label>
                        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <x-input-label for="password_confirmation">
                            {{ __('Confirmar Contraseña') }} <span class="text-red-500">*</span>
                        </x-input-label>
                        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Step 5: Términos y Condiciones -->
            <div x-show="step === 5" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
                <h2 class="text-2xl font-bold text-center mb-6">Términos y Condiciones</h2>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <span class="font-bold">Importante:</span> Para continuar con el proceso de pago, debe leer y aceptar los términos y condiciones. Por favor, lea completamente el documento antes de marcar la casilla.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6 h-96 overflow-y-auto mb-6 shadow-inner prose max-w-none text-gray-800">
                    {!! $terminosHtml !!}
                </div>

                <div class="flex items-center justify-center">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="terms_accepted" x-model="terms_accepted" class="rounded border-gray-300 text-[#004E8B] shadow-sm focus:ring-[#004E8B] h-5 w-5">
                        <span class="ml-3 text-gray-700 font-medium">He leído y acepto los términos y condiciones</span>
                    </label>
                </div>
            </div>

            <!-- Step 6: Pago -->
            <div x-show="step === 6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
                <h2 class="text-2xl font-bold text-center mb-6">Resumen y Pago</h2>
                
                <!-- Resumen Final -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Resumen de Registro</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Paquete</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold" x-text="selected_paquete ? selected_paquete.nombre : ''"></dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Tipo de Registro</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span x-show="tipo_registro === 'doctor'">Con Cédula Profesional</span>
                                    <span x-show="tipo_registro === 'otro'">Sin Cédula Profesional</span>
                                </dd>
                            </div>
                            <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-2">
                                <dt class="text-base font-medium text-gray-900">Total a Pagar</dt>
                                <dd class="mt-1 text-3xl font-bold text-[#004E8B]">
                                    $<span x-text="selected_paquete ? parseFloat(selected_paquete.precio).toFixed(2) : '0.00'"></span>
                                    <span class="text-sm text-gray-500 font-normal">/anual</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Método de Pago -->
                <h3 class="text-lg font-medium text-gray-900 mb-4">Seleccione Método de Pago</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <!-- Tarjeta (CLIP) -->
                    <div @click="payment_method = 'tarjeta'" 
                         :class="{'border-[#004E8B] bg-[#F2F8FD] ring-2 ring-[#004E8B]': payment_method === 'tarjeta', 'border-gray-200 hover:border-[#004E8B]/50': payment_method !== 'tarjeta'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="text-[#004E8B] mb-3">
                            <i class="fas fa-credit-card text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-bold">Pago con Tarjeta</h3>
                        <p class="text-xs text-gray-500 mt-1">Procesado de forma segura vía CLIP</p>
                    </div>

                    <!-- Transferencia -->
                    <div @click="payment_method = 'transferencia'" 
                         :class="{'border-[#004E8B] bg-[#F2F8FD] ring-2 ring-[#004E8B]': payment_method === 'transferencia', 'border-gray-200 hover:border-[#004E8B]/50': payment_method !== 'transferencia'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="text-green-600 mb-3">
                            <i class="fas fa-university text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-bold">Transferencia Bancaria</h3>
                        <p class="text-xs text-gray-500 mt-1">SPEI / Depósito Bancario</p>
                    </div>
                </div>

                <!-- Detalles Transferencia -->
                <div x-show="payment_method === 'transferencia'" x-transition class="bg-gray-50 rounded-lg p-6 border border-gray-200 mb-6">
                    <h4 class="font-bold text-gray-900 mb-4">Datos para Transferencia Bancaria</h4>
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-500">Banco</td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-bold">BBVA</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-500">Beneficiario</td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-bold">Sistema Médico S.A. de C.V.</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-500">CLABE</td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-bold font-mono">012 180 015544332211 5</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-500">Referencia</td>
                                <td class="px-4 py-3 text-sm text-gray-900">Su Email de Registro</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between items-center border-t border-gray-100 pt-6">
                <button type="button" 
                        x-show="step > 1" 
                        @click="prevStep()" 
                        class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#004E8B]">
                    Atrás
                </button>
                <div x-show="step === 1" class="flex-grow"></div> <!-- Spacer for first step -->

                <button type="button" 
                        x-show="step < 6" 
                        @click="nextStep()" 
                        class="px-6 py-2 border border-transparent rounded-md shadow-sm text-white bg-[#004E8B] hover:bg-[#003E6F] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#004E8B]">
                    Siguiente
                </button>

                <button type="submit" 
                        x-show="step === 6" 
                        :disabled="!payment_method"
                        :class="{'opacity-50 cursor-not-allowed': !payment_method}"
                        class="px-6 py-2 border border-transparent rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Finalizar Registro
                </button>
            </div>
        </form>
    </div>

    <script>
        function registerWizard() {
            return {
                step: {{ old('current_step') ?? ($errors->any() ? ($errors->has('terms_accepted') ? 5 : ($errors->has('payment_method') ? 6 : 4)) : 1) }},
                tipo_registro: '{{ old('tipo_registro', '') }}',
                tipo_establecimiento: '{{ old('tipo_establecimiento', '') }}',
                paquete_id: '{{ old('paquete_id', '') }}',
                selected_paquete: null,
                terms_accepted: {{ old('terms_accepted') ? 'true' : 'false' }},
                payment_method: '{{ old('payment_method', '') }}',
                paquetes: @json($paquetes),
                
                init() {
                    if (this.paquete_id) {
                        this.selected_paquete = this.paquetes.find(p => p.id == this.paquete_id);
                    }
                },
                
                getStepTitle(step) {
                    const titles = [
                        'Tipo Registro',
                        'Establecimiento',
                        'Paquete',
                        'Datos Personales',
                        'Términos',
                        'Pago'
                    ];
                    return titles[step - 1];
                },

                get filteredPaquetes() {
                    return this.paquetes.filter(p => {
                        // Filter by establishment type
                        if (p.tipo !== this.tipo_establecimiento) return false;
                        
                        // Filter by doctor/cedula requirement
                        const requiresCedula = this.tipo_registro === 'doctor';
                        const pCedula = p.validar_cedula == 1 || p.validar_cedula === true;
                        
                        return requiresCedula === pCedula;
                    });
                },
                
                nextStep() {
                    if (this.step === 1) {
                        if (!this.tipo_registro) {
                            alert('Por favor seleccione un tipo de registro.');
                            return;
                        }
                    }
                    else if (this.step === 2) {
                        if (!this.tipo_establecimiento) {
                            alert('Por favor seleccione un tipo de establecimiento.');
                            return;
                        }
                    }
                    else if (this.step === 3) {
                        if (!this.paquete_id) {
                            alert('Por favor seleccione un paquete.');
                            return;
                        }
                    }
                    else if (this.step === 4) {
                        // First trigger browser validation (bubbles)
                        if (!document.getElementById('registerForm').reportValidity()) {
                            return;
                        }

                        // Manual validation for visible inputs logic
                        const name = document.getElementById('name').value;
                        const apellido_paterno = document.getElementById('apellido_paterno').value;
                        const email = document.getElementById('email').value;
                        const password = document.getElementById('password').value;
                        const password_confirmation = document.getElementById('password_confirmation').value;

                        if (!name || !apellido_paterno || !email || !password || !password_confirmation) {
                            alert('Por favor complete todos los campos obligatorios.');
                            return;
                        }
                        
                        if (password !== password_confirmation) {
                            alert('Las contraseñas no coinciden.');
                            return;
                        }

                        if (this.tipo_registro === 'doctor') {
                            const cedula = document.getElementById('cedula_profesional').value;
                            if (!cedula) {
                                alert('La cédula profesional es obligatoria para médicos.');
                                return;
                            }
                        }
                    }
                    else if (this.step === 5) {
                        if (!this.terms_accepted) {
                            alert('Debe aceptar los términos y condiciones para continuar.');
                            return;
                        }
                    }
                    
                    this.step++;
                    window.scrollTo(0, 0);
                },
                
                prevStep() {
                    if (this.step > 1) {
                        this.step--;
                        window.scrollTo(0, 0);
                    }
                },
                
                selectPaquete(paquete) {
                    this.paquete_id = paquete.id;
                    this.selected_paquete = paquete;
                }
            }
        }
    </script>
</x-guest-layout>