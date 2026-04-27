<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('wizard.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('wizard.breadcrumbs.setup') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Wizard Container -->
            <div x-data='doctorWizard(@json($actuales), @json($limites))' class="space-y-6">
                
                <!-- Stepper Header -->
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between relative px-4">
                        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-[#F8FAFC] -z-10"></div>
                        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-[#E6F0FF] -z-10 transition-all duration-500" :style="'width: ' + ((currentStep - 1) / (steps.length - 1) * 100) + '%'"></div>
                        
                        <template x-for="(step, index) in steps" :key="index">
                            <div class="flex flex-col items-center cursor-pointer z-10" @click="canGoToStep(index + 1) && (currentStep = index + 1)">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white transition-all duration-300 border-4 border-white shadow-sm"
                                     :class="currentStep > index + 1 ? 'bg-[#27ADFA]' : (currentStep === index + 1 ? 'bg-[#0061F5] ring-2 ring-[#0061F5] ring-offset-2' : 'bg-gray-200 text-gray-400')">
                                    <span x-show="currentStep <= index + 1" x-text="index + 1"></span>
                                    <i x-show="currentStep > index + 1" class="fas fa-check"></i>
                                </div>
                                <span class="mt-2 text-xs font-bold uppercase tracking-wider transition-colors duration-300" 
                                      :class="currentStep === index + 1 ? 'text-[#0061F5]' : (currentStep > index + 1 ? 'text-[#27ADFA]' : 'text-gray-400')"
                                      x-text="step.label"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Content -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 min-h-[400px]">
                    
                    <!-- Step 1: Infraestructura (Clínicas y Consultorios) -->
                    <div x-show="currentStep === 1" class="p-8" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-[#1E293B]">{{ __('wizard.step1.title') }}</h2>
                            <p class="text-gray-500 mt-2">{{ __('wizard.step1.subtitle') }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="bg-[#F8FAFC] p-6 rounded-xl border border-gray-200 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="font-bold text-lg text-[#0061F5]"><i class="fas fa-hospital-alt mr-2"></i> {{ __('wizard.step1.clinicas.title') }}</h3>
                                    <span class="text-sm font-bold bg-[#E6F0FF] text-[#0061F5] px-3 py-1 rounded-full">
                                        <span x-text="actuales.clinicas"></span> / <span x-text="limites.clinicas"></span>
                                    </span>
                                </div>
                                <p class="text-sm text-[#1E293B] mb-6">{{ __('wizard.step1.clinicas.description') }}</p>
                                
                                <div class="space-y-3">
                                    <template x-if="actuales.clinicas < limites.clinicas">
                                        <button @click="openModal('clinica')" class="w-full py-2.5 bg-white border-2 border-dashed border-[#0061F5] text-[#0061F5] rounded-lg hover:bg-[#E6F0FF] hover:text-[#0051CC] transition-all flex items-center justify-center font-bold">
                                            <i class="fas fa-plus mr-2"></i> {{ __('wizard.step1.clinicas.add') }}
                                        </button>
                                    </template>
                                    
                                    <template x-if="actuales.clinicas >= limites.clinicas">
                                        <div class="w-full py-2 bg-gray-100 border border-gray-200 text-gray-500 rounded-lg text-center text-sm font-medium">
                                            <i class="fas fa-info-circle mr-1 text-[#FA7427]"></i> {{ __('wizard.step1.clinicas.limit_reached') }}
                                        </div>
                                    </template>

                                    <template x-if="actuales.clinicas > 0 || actuales.clinicas >= limites.clinicas">
                                        <a href="{{ route('clinicas.index') }}" target="_blank" class="block w-full py-2.5 bg-[#0061F5] text-white rounded-lg hover:bg-[#0051CC] transition-colors text-center font-bold shadow-sm">
                                            <i class="fas fa-edit mr-2"></i> {{ __('wizard.step1.clinicas.manage') }}
                                        </a>
                                    </template>
                                </div>
                            </div>

                            <div class="bg-[#F8FAFC] p-6 rounded-xl border border-gray-200 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="font-bold text-lg text-[#0061F5]"><i class="fas fa-building mr-2"></i> {{ __('wizard.step1.consultorios.title') }}</h3>
                                    <span class="text-sm font-bold bg-[#E6F0FF] text-[#0061F5] px-3 py-1 rounded-full">
                                        <span x-text="actuales.consultorios"></span> / <span x-text="limites.consultorios"></span>
                                    </span>
                                </div>
                                <p class="text-sm text-[#1E293B] mb-6">{{ __('wizard.step1.consultorios.description') }}</p>
                                
                                <div class="space-y-3">
                                    <template x-if="actuales.consultorios < limites.consultorios">
                                        <button @click="openModal('consultorio')" class="w-full py-2.5 bg-white border-2 border-dashed border-[#0061F5] text-[#0061F5] rounded-lg hover:bg-[#E6F0FF] hover:text-[#0051CC] transition-all flex items-center justify-center font-bold">
                                            <i class="fas fa-plus mr-2"></i> {{ __('wizard.step1.consultorios.add') }}
                                        </button>
                                    </template>
                                    
                                    <template x-if="actuales.consultorios >= limites.consultorios">
                                        <div class="w-full py-2 bg-gray-100 border border-gray-200 text-gray-500 rounded-lg text-center text-sm font-medium">
                                            <i class="fas fa-info-circle mr-1 text-[#FA7427]"></i> {{ __('wizard.step1.consultorios.limit_reached') }}
                                        </div>
                                    </template>

                                    <template x-if="actuales.consultorios > 0 || actuales.consultorios >= limites.consultorios">
                                        <a href="{{ route('consultorios.index') }}" target="_blank" class="block w-full py-2.5 bg-[#0061F5] text-white rounded-lg hover:bg-[#0051CC] transition-colors text-center font-bold shadow-sm">
                                            <i class="fas fa-edit mr-2"></i> {{ __('wizard.step1.consultorios.manage') }}
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="currentStep === 2" class="p-8" x-transition>
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-[#1E293B]">{{ __('wizard.step2.title') }}</h2>
                            <p class="text-gray-500 mt-2">{{ __('wizard.step2.subtitle') }}</p>
                        </div>
                        
                        <div class="bg-[#F8FAFC] p-6 rounded-xl border border-gray-200 mb-6 text-center max-w-2xl mx-auto">
                            <div class="text-5xl text-[#FA7427] mb-4"><i class="fas fa-clock"></i></div>
                            <h3 class="text-lg font-bold text-[#1E293B] mb-2">{{ __('wizard.step2.card_title') }}</h3>
                            <p class="text-gray-600 mb-6">{{ __('wizard.step2.card_description') }}</p>
                            
                            <a href="{{ route('horarios.index') }}" target="_blank" class="inline-flex items-center px-6 py-3 bg-[#0061F5] text-white rounded-lg font-bold hover:bg-[#0051CC] transition-colors shadow-md">
                                <i class="fas fa-external-link-alt mr-2"></i> 
                                <span x-text="actuales.horarios > 0 ? '{{ __('wizard.step2.manage') }}' : '{{ __('wizard.step2.configure') }}'"></span>
                            </a>
                            <p class="text-xs text-gray-400 mt-3">{{ __('wizard.step2.opens_in_tab') }}</p>
                        </div>

                        <div class="flex items-center justify-center mt-8">
                            <label class="flex items-center space-x-3 cursor-pointer bg-blue-50 px-4 py-2 rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors">
                                <input type="checkbox" x-model="checks.horarios" class="form-checkbox h-5 w-5 text-[#0061F5] rounded border-gray-300 focus:ring-[#0061F5]">
                                <span class="text-[#1E293B] font-bold">{{ __('wizard.step2.checkbox_label') }}</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="currentStep === 3" class="p-8" x-transition>
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-[#1E293B]">{{ __('wizard.step3.title') }}</h2>
                            <p class="text-gray-500 mt-2">{{ __('wizard.step3.subtitle') }}</p>
                        </div>

                         <div class="bg-[#F8FAFC] p-6 rounded-xl border border-gray-200 mb-6 text-center max-w-2xl mx-auto">
                            <div class="text-5xl text-[#27ADFA] mb-4"><i class="fas fa-file-medical-alt"></i></div>
                            <h3 class="text-lg font-bold text-[#1E293B] mb-2">{{ __('wizard.step3.card_title') }}</h3>
                            <p class="text-gray-600 mb-6">{{ __('wizard.step3.card_description') }}</p>
                            
                            <a href="{{ route('plantillas.index') }}" target="_blank" class="inline-flex items-center px-6 py-3 bg-[#0061F5] text-white rounded-lg font-bold hover:bg-[#0051CC] transition-colors shadow-md">
                                <i class="fas fa-external-link-alt mr-2"></i> 
                                <span x-text="actuales.plantillas > 0 ? '{{ __('wizard.step3.manage') }}' : '{{ __('wizard.step3.create') }}'"></span>
                            </a>
                        </div>

                        <div class="flex items-center justify-center mt-8">
                            <label class="flex items-center space-x-3 cursor-pointer bg-blue-50 px-4 py-2 rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors">
                                <input type="checkbox" x-model="checks.plantillas" class="form-checkbox h-5 w-5 text-[#0061F5] rounded border-gray-300 focus:ring-[#0061F5]">
                                <span class="text-[#1E293B] font-bold">{{ __('wizard.step3.checkbox_label') }}</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="currentStep === 4" class="p-8" x-transition>
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-[#1E293B]">{{ __('wizard.step4.title') }}</h2>
                            <p class="text-gray-500 mt-2">{{ __('wizard.step4.subtitle') }}</p>
                        </div>

                         <div class="bg-[#F8FAFC] p-6 rounded-xl border border-gray-200 mb-6 text-center max-w-2xl mx-auto">
                            <div class="text-5xl text-[#0061F5] mb-4"><i class="fas fa-user-injured"></i></div>
                            <h3 class="text-lg font-bold text-[#1E293B] mb-2">{{ __('wizard.step4.card_title') }}</h3>
                            <p class="text-gray-600 mb-6">{{ __('wizard.step4.card_description') }}</p>
                            
                            <a href="{{ route('pacientes.index') }}" target="_blank" class="inline-flex items-center px-6 py-3 bg-[#0061F5] text-white rounded-lg font-bold hover:bg-[#0051CC] transition-colors shadow-md">
                                <i class="fas fa-external-link-alt mr-2"></i> 
                                <span x-text="actuales.pacientes > 0 ? '{{ __('wizard.step4.manage') }}' : '{{ __('wizard.step4.register') }}'"></span>
                            </a>
                        </div>

                        <div class="flex items-center justify-center mt-8">
                            <label class="flex items-center space-x-3 cursor-pointer bg-blue-50 px-4 py-2 rounded-lg border border-blue-100 hover:bg-blue-100 transition-colors">
                                <input type="checkbox" x-model="checks.pacientes" class="form-checkbox h-5 w-5 text-[#0061F5] rounded border-gray-300 focus:ring-[#0061F5]">
                                <span class="text-[#1E293B] font-bold">{{ __('wizard.step4.checkbox_label') }}</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="currentStep === 5" class="p-8" x-transition>
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold text-[#1E293B]">{{ __('wizard.step5.title') }}</h2>
                            <p class="text-gray-500 mt-2">{{ __('wizard.step5.subtitle') }}</p>
                        </div>

                         <div class="bg-[#F8FAFC] p-6 rounded-xl border border-gray-200 mb-6 max-w-2xl mx-auto hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-lg text-[#0061F5]"><i class="fas fa-users mr-2"></i> {{ __('wizard.step5.card_title') }}</h3>
                                <span class="text-sm font-bold bg-[#E6F0FF] text-[#0061F5] px-3 py-1 rounded-full">
                                    <span x-text="actuales.usuarios"></span> / <span x-text="limites.usuarios"></span>
                                </span>
                            </div>
                            <p class="text-sm text-[#1E293B] mb-6">{{ __('wizard.step5.card_description') }}</p>
                            
                             <div class="space-y-3">
                                 <template x-if="actuales.usuarios < limites.usuarios">
                                    <a href="{{ route('users.index') }}" target="_blank" class="block w-full py-3 bg-white border-2 border-dashed border-[#0061F5] text-[#0061F5] rounded-lg hover:bg-[#E6F0FF] hover:text-[#0051CC] transition-all text-center font-bold">
                                        <i class="fas fa-external-link-alt mr-2"></i> {{ __('wizard.step5.manage') }}
                                    </a>
                                </template>
                                
                                <template x-if="actuales.usuarios >= limites.usuarios">
                                    <div class="w-full py-3 bg-gray-100 border border-gray-200 text-gray-500 rounded-lg text-center text-sm font-medium">
                                        <i class="fas fa-info-circle mr-1 text-[#FA7427]"></i> {{ __('wizard.step5.limit_reached') }}
                                    </div>
                                </template>

                                <template x-if="actuales.usuarios > 0 || actuales.usuarios >= limites.usuarios">
                                    <a href="{{ route('users.index') }}" target="_blank" class="block w-full py-2.5 bg-[#0061F5] text-white rounded-lg hover:bg-[#0051CC] transition-colors text-center font-bold shadow-sm">
                                        <i class="fas fa-edit mr-2"></i> {{ __('wizard.step5.view') }}
                                    </a>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-8 py-5 flex justify-between items-center border-t border-gray-100">
                        <button @click="currentStep--" 
                                x-show="currentStep > 1"
                                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md font-medium hover:bg-gray-100 transition-colors">
                            {{ __('wizard.buttons.back') }}
                        </button>
                        <div class="flex-grow"></div>
                        <button @click="nextStep()" 
                                class="px-8 py-2.5 bg-[#0061F5] text-white rounded-md font-bold hover:bg-[#0051CC] transition-colors shadow-md flex items-center">
                            <span x-text="currentStep === 5 ? '{{ __('wizard.buttons.finish') }}' : '{{ __('wizard.buttons.next') }}'"></span>
                            <i x-show="currentStep < 5" class="fas fa-arrow-right ml-2"></i>
                            <i x-show="currentStep === 5" class="fas fa-check ml-2"></i>
                        </button>
                    </div>
                </div>

            <!-- Modals for Quick Creation -->
            <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    
                    <!-- Overlay -->
                    <div class="fixed inset-0 transition-opacity z-40" aria-hidden="true" @click="modalOpen = false">
                        <div class="absolute inset-0 bg-[#1E293B] opacity-75"></div>
                    </div>

                    <!-- This element is to trick the browser into centering the modal contents. -->
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <!-- Modal Content -->
                    <div class="relative z-50 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" x-text="activeModal === 'clinica' ? '{{ __('wizard.modals.add_clinica') }}' : '{{ __('wizard.modals.add_consultorio') }}'"></h3>
                            
                            <div x-show="activeModal === 'clinica'" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('wizard.modals.clinica.name_label') }}</label>
                                    <input type="text" x-model="formData.clinica.nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('wizard.modals.clinica.address_label') }}</label>
                                    <input x-ref="clinicaAddress" type="text" x-model="formData.clinica.direccion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm" placeholder="{{ __('wizard.modals.clinica.address_placeholder') }}">
                                </div>
                                
                                @if(Auth::user()->hasRole('root') || Auth::user()->active_package_type === 'clinica')
                                <div class="h-64 w-full rounded-lg border border-gray-300 overflow-hidden">
                                    <div x-ref="clinicaMap" class="w-full h-full"></div>
                                </div>
                                @endif

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('wizard.modals.clinica.phone_label') }}</label>
                                    <input type="text" x-model="formData.clinica.telefono" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('wizard.modals.clinica.location_label') }}</label>
                                    <textarea x-model="formData.clinica.ubicacion" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm"></textarea>
                                </div>

                                @role('root')
                                <div class="flex items-center">
                                    <input type="checkbox" x-model="formData.clinica.activo" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                    <label class="ml-2 block text-sm font-medium text-gray-700">{{ __('wizard.modals.clinica.active_label') }}</label>
                                </div>
                                @endrole
                            </div>

                            <div x-show="activeModal === 'consultorio'" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('wizard.modals.consultorio.name_label') }}</label>
                                    <input type="text" x-model="formData.consultorio.nombre" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('wizard.modals.consultorio.address_label') }}</label>
                                    <input x-ref="consultorioAddress" type="text" x-model="formData.consultorio.direccion" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm" placeholder="{{ __('wizard.modals.consultorio.address_placeholder') }}">
                                </div>
                                
                                @if(Auth::user()->hasRole('root') || Auth::user()->active_package_type === 'consultorio')
                                <div class="h-64 w-full rounded-lg border border-gray-300 overflow-hidden">
                                    <div x-ref="consultorioMap" class="w-full h-full"></div>
                                </div>
                                @endif

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('wizard.modals.consultorio.phone_label') }}</label>
                                    <input type="text" x-model="formData.consultorio.telefono" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] sm:text-sm">
                                </div>

                                @role('root')
                                <div class="flex items-center">
                                    <input type="checkbox" x-model="formData.consultorio.activo" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                    <label class="ml-2 block text-sm font-medium text-gray-700">{{ __('wizard.modals.consultorio.active_label') }}</label>
                                </div>
                                @endrole
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" @click="submitForm()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#0061F5] text-base font-medium text-white hover:bg-[#0051CC] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" :disabled="loading">
                                <span x-show="!loading">{{ __('wizard.modals.save') }}</span>
                                <span x-show="loading"><i class="fas fa-spinner fa-spin"></i></span>
                            </button>
                            <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="modalOpen = false" :disabled="loading">
                                {{ __('wizard.modals.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            </div>

        </div>
    </div>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places" async defer></script>
    <script>
        function doctorWizard(actualesData, limitesData) {
            return {
                currentStep: 1,
                steps: [
                    { label: '{{ __('wizard.stepper.step1') }}', icon: 'hospital' },
                    { label: '{{ __('wizard.stepper.step2') }}', icon: 'clock' },
                    { label: '{{ __('wizard.stepper.step3') }}', icon: 'file-medical' },
                    { label: '{{ __('wizard.stepper.step4') }}', icon: 'user-injured' },
                    { label: '{{ __('wizard.stepper.step5') }}', icon: 'users' }
                ],
                actuales: actualesData,
                limites: limitesData,
                checks: {
                    horarios: actualesData.horarios > 0,
                    plantillas: actualesData.plantillas > 0,
                    pacientes: actualesData.pacientes > 0
                },
                modalOpen: false,
                activeModal: null,
                loading: false,
                formData: {
                    clinica: {
                        nombre: '',
                        direccion: '',
                        lat: null,
                        lng: null,
                        telefono: '',
                        ubicacion: '',
                        activo: true
                    },
                    consultorio: {
                        nombre: '',
                        direccion: '',
                        lat: null,
                        lng: null,
                        telefono: '',
                        activo: true
                    }
                },
                map: null,
                marker: null,
                autocomplete: null,

                init() {
                    // Initial setup if needed
                },

                openModal(type) {
                    this.activeModal = type;
                    // Reset form data for the selected type
                    if (type === 'clinica') {
                        this.formData.clinica = { nombre: '', direccion: '', lat: null, lng: null, telefono: '', ubicacion: '', activo: true };
                        // Reset file input if exists
                        
                    } else if (type === 'consultorio') {
                        this.formData.consultorio = { nombre: '', direccion: '', lat: null, lng: null, telefono: '', activo: true };
                    }
                    this.modalOpen = true;
                    
                    // Initialize Map after modal is visible
                    this.$nextTick(() => {
                        this.initMap(type);
                    });
                },

                initMap(type) {
                    const defaultLocation = { lat: 19.4326, lng: -99.1332 };
                    const mapElement = type === 'clinica' ? this.$refs.clinicaMap : this.$refs.consultorioMap;
                    const inputElement = type === 'clinica' ? this.$refs.clinicaAddress : this.$refs.consultorioAddress;

                    // If map element is not present (e.g. package restriction), we can still try to init autocomplete on the input
                    // But if strict restriction is desired, we might skip it. 
                    // Current logic: If map is missing, we skip everything including autocomplete.
                    if (!mapElement || !inputElement) return;

                    // Ensure Google Maps is loaded
                    if (typeof google === 'undefined') {
                        console.error('Google Maps API not loaded');
                        return;
                    }

                    this.map = new google.maps.Map(mapElement, {
                        center: defaultLocation,
                        zoom: 13,
                        mapTypeControl: false,
                        streetViewControl: false
                    });

                    this.marker = new google.maps.Marker({
                        map: this.map,
                        draggable: true,
                        position: defaultLocation
                    });

                    // Autocomplete
                    this.autocomplete = new google.maps.places.Autocomplete(inputElement);
                    this.autocomplete.bindTo('bounds', this.map);
                    // Prevent form submission on Enter
                    inputElement.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter') e.preventDefault();
                    });

                    this.autocomplete.addListener('place_changed', () => {
                        const place = this.autocomplete.getPlace();
                        if (!place.geometry || !place.geometry.location) {
                            return;
                        }

                        // Update map and marker
                        if (place.geometry.viewport) {
                            this.map.fitBounds(place.geometry.viewport);
                        } else {
                            this.map.setCenter(place.geometry.location);
                            this.map.setZoom(17);
                        }

                        this.marker.setPosition(place.geometry.location);

                        // Update formData
                        const lat = place.geometry.location.lat();
                        const lng = place.geometry.location.lng();
                        const address = place.formatted_address;

                        if (type === 'clinica') {
                            this.formData.clinica.lat = lat;
                            this.formData.clinica.lng = lng;
                            this.formData.clinica.direccion = address;
                        } else {
                            this.formData.consultorio.lat = lat;
                            this.formData.consultorio.lng = lng;
                            this.formData.consultorio.direccion = address;
                        }
                    });

                    // Marker drag listener
                    this.marker.addListener('dragend', (event) => {
                        const lat = event.latLng.lat();
                        const lng = event.latLng.lng();

                        if (type === 'clinica') {
                            this.formData.clinica.lat = lat;
                            this.formData.clinica.lng = lng;
                        } else {
                            this.formData.consultorio.lat = lat;
                            this.formData.consultorio.lng = lng;
                        }
                    });
                },

                async submitForm() {
                    if (!this.activeModal) return;

                    this.loading = true;
                    const type = this.activeModal;
                    const url = type === 'clinica' 
                        ? "{{ route('doctor.wizard.store_clinica') }}" 
                        : "{{ route('doctor.wizard.store_consultorio') }}";
                    
                    const data = this.formData[type];
                    const formData = new FormData();

                    // Append fields
                    for (const key in data) {
                        if (data[key] !== null && data[key] !== undefined) {
                            // Convert boolean to 1/0 for backend
                            if (typeof data[key] === 'boolean') {
                                formData.append(key, data[key] ? '1' : '0');
                            } else {
                                formData.append(key, data[key]);
                            }
                        }
                    }

                    // Handle file upload for clinica
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                                // No Content-Type header for FormData, browser sets boundary
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            // Update counters
                            if (result.actuales) {
                                if (result.actuales.clinicas !== undefined) this.actuales.clinicas = result.actuales.clinicas;
                                if (result.actuales.consultorios !== undefined) this.actuales.consultorios = result.actuales.consultorios;
                            } else {
                                if (type === 'clinica') this.actuales.clinicas++;
                                if (type === 'consultorio') this.actuales.consultorios++;
                            }
                            
                            this.modalOpen = false;
                            // Reset form
                            if (type === 'clinica') {
                                this.formData.clinica = { nombre: '', direccion: '', lat: null, lng: null, telefono: '', ubicacion: '', activo: true };
                                
                            } else {
                                this.formData.consultorio = { nombre: '', direccion: '', lat: null, lng: null, telefono: '', activo: true };
                            }
                        } else {
                            alert('Error al guardar: ' + (result.message || 'Error desconocido'));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        if (error.response) {
                             // Attempt to parse validation errors
                             alert('Error de validación: Verifique los datos.');
                        } else {
                             alert('Ocurrió un error al procesar la solicitud.');
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                canGoToStep(step) {
                    if (step > this.currentStep) {
                        if (this.currentStep === 1) {
                            // Optional validation for step 1
                            return true; 
                        }
                    }
                    return true;
                },

                nextStep() {
                    if (this.currentStep < 5) {
                        if (this.currentStep === 2 && !this.checks.horarios && this.actuales.horarios === 0) {
                            alert('Por favor configura tus horarios o confirma que ya lo has hecho.');
                            return;
                        }
                        if (this.currentStep === 3 && !this.checks.plantillas && this.actuales.plantillas === 0) {
                            alert('Por favor crea una plantilla o confirma que ya lo has hecho.');
                            return;
                        }
                        if (this.currentStep === 4 && !this.checks.pacientes && this.actuales.pacientes === 0) {
                            alert('Por favor registra un paciente o confirma que ya lo has hecho.');
                            return;
                        }
                        
                        this.currentStep++;
                        window.scrollTo(0, 0);
                    } else {
                        window.location.href = "{{ route('dashboard') }}";
                    }
                }
            }
        }
    </script>
</x-app-layout>
