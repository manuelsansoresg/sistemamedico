<x-guest-layout>
    <x-slot name="maxWidth">sm:max-w-5xl</x-slot>
    <x-slot name="header">
        <div class="bg-[#0061F5] p-8 text-center">
            <div class="flex justify-center mb-4">
                <i class="fas fa-user-md text-white text-4xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">{{ __('auth.registration.title') }}</h1>
            <p class="text-[#E1EFF9] text-lg">{{ __('auth.registration.subtitle') }}</p>
        </div>
    </x-slot>

    <div x-data="registerWizard()" x-init="init()" class="p-4 doctor-register">
        
        <!-- Progress Indicator -->
        <div class="mb-8">
            <div class="flex items-center justify-between relative">
                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 -z-10"></div>
                <template x-for="i in 6">
                    <div class="relative z-10 flex flex-col items-center">
                        <div :class="{'bg-[#0061F5] text-white': step >= i, 'bg-gray-200 text-gray-500': step < i}"
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
                            <h3 class="text-sm font-medium text-red-800">{{ __('auth.registration.errors_title') }}</h3>
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
            <input type="hidden" name="card_token_id" x-ref="cardTokenInput">

            <!-- Step 1: Tipo de Registro -->
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                <h2 class="text-2xl font-bold text-center mb-6">{{ __('auth.registration.sections.register_type') }} <span class="text-red-500 text-lg">*</span></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Doctor -->
                    <div @click="tipo_registro = 'doctor'" 
                         :class="{'border-[#0061F5] bg-[#F2F8FD] ring-2 ring-[#0061F5]': tipo_registro === 'doctor', 'border-gray-200 hover:border-[#0061F5]/50': tipo_registro !== 'doctor'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="w-16 h-16 bg-[#E1EFF9] rounded-full flex items-center justify-center mb-4 text-[#0061F5]">
                            <i class="fas fa-user-md text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2">{{ __('auth.registration.options.doctor') }}</h3>
                        <p class="text-gray-500 text-sm">{{ __('auth.registration.options.doctor_description') }}</p>
                    </div>

                    <!-- Otro Profesional -->
                    <div @click="tipo_registro = 'otro'" 
                         :class="{'border-[#0061F5] bg-[#F2F8FD] ring-2 ring-[#0061F5]': tipo_registro === 'otro', 'border-gray-200 hover:border-[#0061F5]/50': tipo_registro !== 'otro'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="w-16 h-16 bg-[#27ADFA]/10 rounded-full flex items-center justify-center mb-4 text-[#27ADFA]">
                            <i class="fas fa-user-nurse text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2">{{ __('auth.registration.options.other_professional') }}</h3>
                        <p class="text-gray-500 text-sm">{{ __('auth.registration.options.other_professional_description') }}</p>
                    </div>
                </div>
            </div>

            <!-- Step 2: Tipo de Establecimiento -->
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
                <h2 class="text-2xl font-bold text-center mb-6">{{ __('auth.registration.sections.establishment_type') }} <span class="text-red-500 text-lg">*</span></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Clinica -->
                    <div @click="tipo_establecimiento = 'clinica'" 
                         :class="{'border-[#0061F5] bg-[#F2F8FD] ring-2 ring-[#0061F5]': tipo_establecimiento === 'clinica', 'border-gray-200 hover:border-[#0061F5]/50': tipo_establecimiento !== 'clinica'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 bg-[#E1EFF9] text-[#0061F5]">
                            <i class="fas fa-hospital text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2">{{ __('auth.registration.options.clinic') }}</h3>
                        <p class="text-gray-500 text-sm">{{ __('auth.registration.options.clinic_description') }}</p>
                    </div>

                    <!-- Consultorio -->
                    <div @click="tipo_establecimiento = 'consultorio'" 
                         :class="{'border-[#0061F5] bg-[#F2F8FD] ring-2 ring-[#0061F5]': tipo_establecimiento === 'consultorio', 'border-gray-200 hover:border-[#0061F5]/50': tipo_establecimiento !== 'consultorio'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 bg-[#E1EFF9] text-[#0061F5]">
                            <i class="fas fa-clinic-medical text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-bold mb-2">{{ __('auth.registration.options.office') }}</h3>
                        <p class="text-gray-500 text-sm">{{ __('auth.registration.options.office_description') }}</p>
                    </div>
                </div>
            </div>

            <!-- Step 3: Paquetes -->
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
                <h2 class="text-2xl font-bold text-center mb-6">{{ __('auth.registration.sections.package') }} <span class="text-red-500 text-lg">*</span></h2>
                
                <div x-show="filteredPaquetes.length === 0" class="text-center py-10 text-gray-500">
                    <p>{{ __('auth.registration.package.no_available') }}</p>
                    <button type="button" @click="step = 1" class="text-[#0061F5] hover:underline mt-2">{{ __('auth.registration.package.back_to_start') }}</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <template x-for="paquete in filteredPaquetes" :key="paquete.id">
                        <div @click="selectPaquete(paquete)" 
                             :class="{'border-[#0061F5] bg-[#F2F8FD] ring-2 ring-[#0061F5] shadow-lg': paquete_id === paquete.id, 'border-gray-200 hover:border-[#0061F5]/50 shadow-sm': paquete_id !== paquete.id}"
                             class="cursor-pointer border rounded-xl p-6 flex flex-col relative transition-all duration-200 bg-white">
                            
                            <div x-show="paquete_id === paquete.id" class="absolute top-0 right-0 -mt-2 -mr-2 bg-[#0061F5] text-white rounded-full p-1 shadow">
                                <i class="fas fa-check"></i>
                            </div>

                            <h3 class="text-xl font-bold mb-2 text-gray-900" x-text="paquete.nombre"></h3>
                            <div class="text-3xl font-bold text-[#0061F5] mb-4">
                                $<span x-text="parseFloat(paquete.precio).toFixed(2)"></span>
                                <span class="text-sm text-gray-500 font-normal">{{ __('auth.registration.package.annual_suffix') }}</span>
                            </div>
                            
                            <div class="border-t border-gray-100 my-4"></div>

                            <div class="text-sm text-gray-600 mb-2 font-bold text-[#1E293B]">{{ __('auth.registration.package.included_limits') }}</div>
                            <ul class="text-sm text-gray-600 space-y-2 mb-6 flex-grow">
                                <template x-for="cat in (paquete.catalogos || [])" :key="cat.id">
                                    <template x-if="cat.pivot && cat.pivot.cantidad_maxima > 0">
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-[#27ADFA] mt-1 mr-2"></i>
                                            <span x-text="cat.nombre + ' (' + @json(__('auth.registration.package.maximum')) + ' ' + cat.pivot.cantidad_maxima + ')'"></span>
                                        </li>
                                    </template>
                                </template>
                                <li class="flex items-start">
                                    <i class="fas fa-info-circle text-[#0061F5] mt-1 mr-2"></i>
                                    <span x-text="paquete.validar_cedula ? @json(__('auth.registration.options.requires_professional_id')) : @json(__('auth.registration.options.does_not_require_professional_id'))"></span>
                                </li>
                            </ul>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Step 4: Información Personal -->
            <div x-show="step === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
                <h2 class="text-2xl font-bold text-center mb-6">{{ __('auth.registration.sections.personal_info') }}</h2>
                
                <p class="text-sm text-gray-500 mb-4 text-right">
                    <span class="text-red-500">*</span> {{ __('auth.registration.fields.required_fields') }}
                </p>

                <!-- Resumen Paquete Selection -->
                <div class="bg-[#F2F8FD] border border-[#E1EFF9] rounded-lg p-4 mb-6 flex justify-between items-center" x-show="selected_paquete">
                    <div>
                    <p class="text-sm text-[#0061F5] font-medium">{{ __('auth.registration.fields.selected_package') }}</p>
                    <p class="font-bold text-[#1E293B]" x-text="selected_paquete ? selected_paquete.nombre : ''"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-[#0061F5] font-medium">{{ __('auth.registration.fields.cost') }}</p>
                        <p class="font-bold text-[#1E293B]">$<span x-text="selected_paquete ? parseFloat(selected_paquete.precio).toFixed(2) : '0.00'"></span></p>
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 border-b pb-2 mb-4">{{ __('auth.registration.sections.personal_info') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-bold text-gray-700">{{ __('common.name') }}</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]"
                                required
                            >
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div>
                            <label for="apellido_paterno" class="block text-sm font-bold text-gray-700">{{ __('common.last_name') }}</label>
                            <input
                                type="text"
                                name="apellido_paterno"
                                id="apellido_paterno"
                                value="{{ old('apellido_paterno') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]"
                                required
                            >
                            <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
                        </div>
                        <div>
                            <label for="apellido_materno" class="block text-sm font-bold text-gray-700">{{ __('common.mother_last_name') }}</label>
                            <input
                                type="text"
                                name="apellido_materno"
                                id="apellido_materno"
                                value="{{ old('apellido_materno') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]"
                            >
                            <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
                        </div>
                        <div>
                            <label for="telefono" class="block text-sm font-bold text-gray-700">{{ __('common.phone') }}</label>
                            <input
                                type="text"
                                name="telefono"
                                id="telefono"
                                value="{{ old('telefono') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]"
                            >
                            <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-bold text-gray-700">{{ __('common.email') }}</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]"
                                required
                            >
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>
                </div>
                
                <!-- Doctor Specific Fields -->
                <div x-show="tipo_registro === 'doctor'" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 pt-6">
                    <div>
                        <x-input-label for="cedula_profesional">
                             {{ __('common.professional_id') }} <span class="text-red-500">*</span>
                        </x-input-label>
                        <x-text-input
                            id="cedula_profesional"
                            type="text"
                            name="cedula_profesional"
                            :value="old('cedula_profesional')"
                        />
                        <x-input-error :messages="$errors->get('cedula_profesional')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="especialidad_id">
                            {{ __('common.specialty') }} <span class="text-red-500">*</span>
                        </x-input-label>
                        <select
                            id="especialidad_id"
                            name="especialidad_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]"
                        >
                            <option value="">{{ __('especialidades.select') }}</option>
                            @foreach($especialidades as $especialidad)
                                <option value="{{ $especialidad->id }}" {{ old('especialidad_id') == $especialidad->id ? 'selected' : '' }}>{{ $especialidad->nombre }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('especialidad_id')" class="mt-2" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 mt-6 border-t border-gray-200 pt-6">
                    <!-- Password -->
                    <div>
                        <x-input-label for="password">
                            {{ __('common.password') }} <span class="text-red-500">*</span>
                        </x-input-label>
                        <x-text-input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <x-input-label for="password_confirmation">
                            {{ __('common.confirm_password') }} <span class="text-red-500">*</span>
                        </x-input-label>
                        <x-text-input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                        />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- Step 5: Términos y Condiciones -->
            <div x-show="step === 5" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
                <h2 class="text-2xl font-bold text-center mb-6">{{ __('auth.registration.sections.terms') }}</h2>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <span class="font-bold">{{ __('auth.registration.terms.important') }}</span> {{ __('auth.registration.terms.instructions') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6 h-96 overflow-y-auto mb-6 shadow-inner prose max-w-none text-gray-800">
                    {!! $terminosHtml !!}
                </div>

                <div class="flex items-center justify-center">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="terms_accepted" x-model="terms_accepted" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:ring-[#0061F5] h-5 w-5">
                        <span class="ml-3 text-gray-700 font-medium">{{ __('auth.registration.terms.accepted') }} <span class="text-red-500">*</span></span>
                    </label>
                </div>
            </div>

            <!-- Step 6: Pago -->
            <div x-show="step === 6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0" style="display: none;">
                <h2 class="text-2xl font-bold text-center mb-6">{{ __('auth.registration.sections.summary_payment') }}</h2>
                
                <!-- Resumen Final -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('auth.registration.sections.registration_summary') }}</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">{{ __('auth.registration.fields.package') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-bold" x-text="selected_paquete ? selected_paquete.nombre : ''"></dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">{{ __('auth.registration.fields.registration_type') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span x-show="tipo_registro === 'doctor'">{{ __('auth.registration.options.with_professional_id') }}</span>
                                    <span x-show="tipo_registro === 'otro'">{{ __('auth.registration.options.without_professional_id') }}</span>
                                </dd>
                            </div>
                            <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-2">
                                <dt class="text-base font-medium text-gray-900">{{ __('auth.registration.fields.total_to_pay') }}</dt>
                                <dd class="mt-1 text-3xl font-bold text-[#0061F5]">
                                    $<span x-text="selected_paquete ? parseFloat(selected_paquete.precio).toFixed(2) : '0.00'"></span>
                                    <span class="text-sm text-gray-500 font-normal">{{ __('auth.registration.package.annual_suffix') }}</span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Método de Pago -->
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('auth.registration.sections.payment_method') }} <span class="text-red-500">*</span></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Tarjeta (CLIP) -->
                    <div @click="payment_method = 'tarjeta'; if (step === 6) { initClip(); }" 
                         :class="{'border-[#0061F5] bg-[#F2F8FD] ring-2 ring-[#0061F5]': payment_method === 'tarjeta', 'border-gray-200 hover:border-[#0061F5]/50': payment_method !== 'tarjeta'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="text-[#0061F5] mb-3">
                            <i class="fas fa-credit-card text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-bold">{{ __('auth.registration.options.card_payment') }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ __('auth.registration.options.card_payment_description') }}</p>
                    </div>

                    <!-- Transferencia -->
                    <div @click="payment_method = 'transferencia'" 
                         :class="{'border-[#0061F5] bg-[#F2F8FD] ring-2 ring-[#0061F5]': payment_method === 'transferencia', 'border-gray-200 hover:border-[#0061F5]/50': payment_method !== 'transferencia'}"
                         class="cursor-pointer border rounded-xl p-6 flex flex-col items-center text-center transition-all duration-200">
                        <div class="text-[#27ADFA] mb-3">
                            <i class="fas fa-university text-4xl"></i>
                        </div>
                        <h3 class="text-lg font-bold">{{ __('auth.registration.options.bank_transfer') }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ __('auth.registration.options.bank_transfer_description') }}</p>
                    </div>
                </div>

                <!-- Formulario de Tarjeta (Clip) -->
                <div x-show="payment_method === 'tarjeta'" x-transition class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-3">{{ __('auth.registration.sections.card_data') }}</h4>
                    <p class="text-sm text-gray-500 mb-4">{{ __('auth.registration.payment.card_secure') }}</p>
                    <div id="clip-checkout" class="border rounded-md p-4"></div>
                    <p class="text-xs text-gray-400 mt-2">{{ __('auth.registration.payment.accepted_cards') }}</p>
                </div>

                <!-- Detalles Transferencia -->
                <div x-show="payment_method === 'transferencia'" x-transition class="bg-gray-50 rounded-lg p-6 border border-gray-200 mb-6">
                    <h4 class="font-bold text-gray-900 mb-4">{{ __('auth.registration.sections.bank_transfer_data') }}</h4>
                    <table class="min-w-full divide-y divide-gray-200">
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-500">{{ __('auth.registration.fields.bank') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-bold">BBVA</td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-500">{{ __('auth.registration.fields.beneficiary') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-bold">Sistema Médico S.A. de C.V.</td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-500">CLABE</td>
                                <td class="px-4 py-3 text-sm text-gray-900 font-bold font-mono">012 180 015544332211 5</td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-500">{{ __('auth.registration.fields.reference') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ __('auth.registration.payment.email_reference') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 flex justify-between items-center border-t border-gray-100 pt-6">
                <div class="flex items-center space-x-3">
                    <a href="#"
                       x-show="step > 1"
                       @click.prevent="prevStep()"
                       class="inline-flex items-center px-7 py-3.5 bg-white text-[#0061F5] font-bold rounded-lg border border-[#0061F5] hover:bg-gray-50 transition-colors">
                        {{ __('auth.registration.buttons.back') }}
                    </a>
                </div>

                <div class="flex items-center space-x-3 ml-auto">
                    <a href="#"
                       x-show="step < 6"
                       @click.prevent="nextStep()"
                       class="inline-flex items-center px-7 py-3.5 bg-[#0061F5] text-white font-bold rounded-lg hover:bg-[#0051CC] transition-colors">
                        {{ __('auth.registration.buttons.next') }}
                    </a>

                    <a href="#"
                       x-show="step === 6"
                       :class="{'opacity-50 cursor-not-allowed': !payment_method || isSubmitting}"
                       @click.prevent="if (payment_method && !isSubmitting) { finishRegistration() }"
                       class="inline-flex items-center px-7 py-3.5 bg-[#27ADFA] text-white font-bold rounded-lg hover:bg-[#0061F5] transition-colors">
                        <span x-show="!isSubmitting">{{ __('auth.registration.buttons.finish') }}</span>
                        <span x-show="isSubmitting" class="flex items-center space-x-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span>{{ __('auth.registration.payment.processing') }}</span>
                        </span>
                    </a>
                </div>
            </div>
        </form>

        <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-gray-900/50"></div>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex items-center mb-3">
                    <div class="flex-shrink-0 mr-3">
                        <i class="fas fa-check-circle text-green-500 text-2xl" x-show="modalType === 'success'"></i>
                        <i class="fas fa-exclamation-circle text-red-500 text-2xl" x-show="modalType === 'error'"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900" x-text="modalTitle"></h2>
                </div>
                <p class="text-sm text-gray-700 mb-5" x-text="modalMessage"></p>
                <div class="flex justify-end space-x-2">
                    <button type="button"
                            class="px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700"
                            x-show="modalType === 'error'"
                            @click="modalOpen = false">
                        {{ __('auth.registration.buttons.close') }}
                    </button>
                    <button type="button"
                            class="px-4 py-2 rounded-md bg-[#0061F5] text-sm text-white font-semibold"
                            x-show="modalType === 'success'"
                            @click="if (modalRedirect) { window.location = modalRedirect }">
                        {{ __('auth.registration.buttons.go_dashboard') }}
                    </button>
                </div>
            </div>
        </div>

        <div x-cloak x-show="isSubmitting" class="fixed inset-0 z-40 flex items-center justify-center">
            <div class="fixed inset-0 bg-white/60"></div>
            <div class="relative bg-white rounded-lg shadow-md px-6 py-4 flex items-center space-x-3">
                <svg class="animate-spin h-5 w-5 text-[#0061F5]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-700">{{ __('auth.registration.payment.processing_wait') }}</span>
            </div>
        </div>
    </div>

    <!-- SDK de Clip -->
    <script src="https://sdk.clip.mx/js/clip-sdk.js"></script>

    <script>
        function registerWizard() {
            return {
                step: @php
                    $initialStep = 1;
                    if ($errors->any()) {
                        if ($errors->has('tipo_registro')) $initialStep = 1;
                        elseif ($errors->has('tipo_establecimiento')) $initialStep = 2;
                        elseif ($errors->has('paquete_id')) $initialStep = 3;
                        elseif ($errors->has('terms_accepted')) $initialStep = 5;
                        elseif ($errors->has('payment_method') || $errors->has('payment')) $initialStep = 6;
                        else $initialStep = 4; // Default for personal info errors (name, email, password, etc.)
                    } elseif (old('current_step')) {
                        $initialStep = old('current_step');
                    }
                    echo $initialStep;
                @endphp,
                tipo_registro: '{{ old('tipo_registro', '') }}',
                tipo_establecimiento: '{{ old('tipo_establecimiento', '') }}',
                paquete_id: '{{ old('paquete_id', '') }}',
                selected_paquete: null,
                terms_accepted: {{ old('terms_accepted') ? 'true' : 'false' }},
                payment_method: '{{ old('payment_method', '') }}',
                paquetes: @json($paquetes),
                clipApiKey: @json($clipApiKey ?? null),
                clipCard: null,
                clipInitialized: false,
                isSubmitting: false,
                modalOpen: false,
                modalTitle: '',
                modalMessage: '',
                modalType: 'success',
                modalRedirect: null,
                
                init() {
                    if (this.paquete_id) {
                        this.selected_paquete = this.paquetes.find(p => p.id == this.paquete_id);
                    }
                    // Si ya está en paso 6 y tarjeta, inicializa Clip
                    if (this.step === 6 && this.payment_method === 'tarjeta') {
                        this.initClip();
                    }
                },
                
                getStepTitle(step) {
                    const titles = [
                        @json(__('auth.registration.steps.register_type')),
                        @json(__('auth.registration.steps.establishment')),
                        @json(__('auth.registration.steps.package')),
                        @json(__('auth.registration.steps.personal_data')),
                        @json(__('auth.registration.steps.terms')),
                        @json(__('auth.registration.steps.payment'))
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
                            alert(@json(__('auth.registration.validation.select_register_type')));
                            return;
                        }
                    }
                    else if (this.step === 2) {
                        if (!this.tipo_establecimiento) {
                            alert(@json(__('auth.registration.validation.select_establishment_type')));
                            return;
                        }
                    }
                    else if (this.step === 3) {
                        if (!this.paquete_id) {
                            alert(@json(__('auth.registration.validation.select_package')));
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
                            alert(@json(__('auth.registration.validation.required_fields')));
                            return;
                        }
                        
                        if (password !== password_confirmation) {
                            alert(@json(__('auth.registration.validation.password_mismatch')));
                            return;
                        }

                        if (this.tipo_registro === 'doctor') {
                            const cedula = document.getElementById('cedula_profesional').value;
                            if (!cedula) {
                                alert(@json(__('auth.registration.validation.professional_id_required')));
                                return;
                            }
                        }
                    }
                    else if (this.step === 5) {
                        if (!this.terms_accepted) {
                            alert(@json(__('auth.registration.validation.terms_required')));
                            return;
                        }
                    }
                    
                    this.step++;
                    window.scrollTo(0, 0);
                    // Al entrar al paso 6 con tarjeta, inicializa Clip
                    if (this.step === 6 && this.payment_method === 'tarjeta') {
                        this.initClip();
                    }
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
                },

                initClip() {
                    if (this.clipInitialized) return;
                    if (!this.clipApiKey || typeof ClipSDK === 'undefined') {
                        console.warn('Clip SDK o API Key no disponible');
                        return;
                    }
                    try {
                        const clip = new ClipSDK(this.clipApiKey);
                        this.clipCard = clip.element.create("Card", {
                            locale: "es",
                            theme: "light",
                        });
                        this.clipCard.mount("clip-checkout");
                        this.clipInitialized = true;
                    } catch (e) {
                        console.error('Error inicializando Clip SDK', e);
                    }
                },

                async finishRegistration() {
                    if (this.payment_method === 'transferencia') {
                        this.$refs.registerForm.submit();
                        return;
                    }

                    if (this.payment_method === 'tarjeta') {
                        this.isSubmitting = true;
                        if (!this.clipInitialized || !this.clipCard) {
                            this.initClip();
                        }
                        if (!this.clipCard) {
                            this.isSubmitting = false;
                            this.modalTitle = @json(__('auth.registration.modal.error_title'));
                            this.modalMessage = @json(__('auth.registration.payment_errors.card_form_init_failed'));
                            this.modalType = 'error';
                            this.modalRedirect = null;
                            this.modalOpen = true;
                            return;
                        }
                        try {
                            const cardToken = await this.clipCard.cardToken();
                            if (!cardToken || !cardToken.id) {
                                this.isSubmitting = false;
                                this.modalTitle = @json(__('auth.registration.modal.payment_rejected_title'));
                                this.modalMessage = @json(__('auth.registration.payment_errors.card_token_failed'));
                                this.modalType = 'error';
                                this.modalRedirect = null;
                                this.modalOpen = true;
                                return;
                            }
                            this.$refs.cardTokenInput.value = cardToken.id;
                        } catch (error) {
                            this.isSubmitting = false;
                            this.modalTitle = @json(__('auth.registration.modal.error_title'));
                            this.modalMessage = error && error.message ? error.message : @json(__('auth.registration.payment_errors.card_token_error'));
                            this.modalType = 'error';
                            this.modalRedirect = null;
                            this.modalOpen = true;
                            return;
                        }
                    }

                    const form = this.$refs.registerForm;
                    const formData = new FormData(form);
                    const csrf = form.querySelector('input[name=_token]').value;

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        let data = null;
                        try {
                            data = await response.json();
                        } catch (e) {}

                        this.isSubmitting = false;

                        if (response.ok && data && data.status === 'success') {
                            this.modalTitle = @json(__('auth.registration.modal.success_title'));
                            this.modalMessage = data.message || @json(__('auth.registration.modal.success_message'));
                            this.modalType = 'success';
                            this.modalRedirect = data.redirect || '{{ route('dashboard') }}';
                            this.modalOpen = true;
                        } else {
                            let message = @json(__('auth.registration.payment_errors.card_processing_failed'));
                            if (data) {
                                if (data.message) {
                                    message = data.message;
                                } else if (data.errors && data.errors.payment && data.errors.payment[0]) {
                                    message = data.errors.payment[0];
                                }
                            }
                            this.modalTitle = @json(__('auth.registration.modal.payment_rejected_title'));
                            this.modalMessage = message;
                            this.modalType = 'error';
                            this.modalRedirect = null;
                            this.modalOpen = true;
                        }
                    } catch (e) {
                        this.isSubmitting = false;
                        this.modalTitle = @json(__('auth.registration.modal.error_title'));
                        this.modalMessage = @json(__('auth.registration.payment_errors.server_connection_failed'));
                        this.modalType = 'error';
                        this.modalRedirect = null;
                        this.modalOpen = true;
                    }
                }
            }
        }
    </script>
</x-guest-layout>
