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
                            <a href="{{ route('citas.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">{{ __('citas.breadcrumbs.index') }}</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('citas.breadcrumbs.create') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-visible shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-6">{{ __('citas.headings.create') }}</h2>

                    <div x-data="appointmentForm()" class="space-y-6">
                        
                        <!-- Error Messages -->
                        <template x-if="errorMessage">
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline" x-text="errorMessage"></span>
                            </div>
                        </template>

                        <form action="{{ route('citas.store') }}" method="POST" @submit.prevent="submitForm" autocomplete="off">
                            @csrf
                            
                            <!-- 1. Seleccionar Doctor -->
                            <div class="mb-6">
                                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('citas.steps.select_doctor') }}</label>
                                
                                <div x-show="isDoctorFixed" class="p-4 bg-gray-50 border border-gray-200 rounded-lg flex items-center mb-2">
                                    <div class="bg-[#E6F0FF] p-2 rounded-full mr-3">
                                        <i class="fas fa-user-md text-[#0061F5]"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800" x-text="searchDoctor"></p>
                                        <p class="text-sm text-gray-500">{{ __('citas.helpers.auto_assigned') }}</p>
                                    </div>
                                    <input type="hidden" name="doctor_id" x-model="selectedDoctorId">
                                </div>

                                <div class="relative" x-show="!isDoctorFixed" @click.outside="doctorDropdownOpen = false">
                                    <button type="button"
                                            @click="toggleDoctorDropdown()"
                                            class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-3 text-left text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none flex items-center justify-between bg-white"
                                            :class="{'border-green-500': selectedDoctor, 'border-[#0061F5] ring-1 ring-[#0061F5]': doctorDropdownOpen}">
                                        <span x-text="selectedDoctor ? doctorDisplayName(selectedDoctor) : '{{ __('citas.placeholders.select_doctor') }}'"
                                              :class="selectedDoctor ? 'text-gray-800' : 'text-gray-500'"></span>
                                        <i class="fas fa-chevron-down text-gray-400 ml-3 transition-transform" :class="{'rotate-180': doctorDropdownOpen}"></i>
                                    </button>
                                    <input type="hidden" name="doctor_id" x-model="selectedDoctorId">
                                     
                                    <!-- Results Dropdown -->
                                    <div x-show="doctorDropdownOpen"
                                         x-transition
                                         class="absolute z-50 bg-white w-full border border-gray-200 rounded-lg shadow-xl mt-2 overflow-hidden">
                                        <div class="p-2 border-b border-gray-100 bg-gray-50">
                                            <input type="text"
                                                   x-model="searchDoctor"
                                                   @input="filterDoctors()"
                                                   @keydown.escape="doctorDropdownOpen = false"
                                                   x-ref="doctorSearchInput"
                                                   autocomplete="new-password"
                                                   autocorrect="off"
                                                   autocapitalize="none"
                                                   spellcheck="false"
                                                   placeholder="{{ __('citas.placeholders.search_doctor') }}"
                                                   class="appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none bg-white">
                                        </div>
                                        <div class="max-h-60 overflow-y-auto">
                                            <template x-for="doctor in doctors" :key="doctor.id">
                                                <button type="button" @click="selectDoctor(doctor)" class="w-full p-3 hover:bg-[#E6F0FF] cursor-pointer border-b border-gray-100 text-left transition-colors last:border-b-0">
                                                    <span class="font-semibold text-gray-800" x-text="doctorDisplayName(doctor)"></span>
                                                    <span class="block text-xs text-gray-500" x-show="doctor.email" x-text="doctor.email"></span>
                                                </button>
                                            </template>
                                            <div x-show="doctors.length === 0" class="p-4 text-sm text-gray-500 text-center">
                                                {{ __('citas.select.no_doctors') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div x-show="!isDoctorFixed && selectedDoctor" class="mt-2 text-green-600 font-semibold">
                                    {{ __('citas.labels.doctor_selected') }} <span x-text="selectedDoctor.name + ' ' + selectedDoctor.apellido_paterno"></span>
                                    <button type="button" @click="resetDoctor" class="ml-2 text-red-500 text-sm underline">{{ __('citas.buttons.change') }}</button>
                                </div>
                            </div>

                            <!-- 2. Consultorio y Clinica (Visible only if Doctor selected) -->
                            <div x-show="selectedDoctorId" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" x-transition>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('citas.steps.office') }}</label>
                                    <select name="consultorio_id" x-model="selectedConsultorioId" @change="fetchSlotsIfReady" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none">
                                        <option value="">{{ __('citas.select.office') }}</option>
                                        <template x-for="consultorio in consultorios" :key="consultorio.id">
                                            <option :value="consultorio.id" x-text="consultorio.nombre"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('citas.steps.clinic') }}</label>
                                    <select name="clinica_id" x-model="selectedClinicaId" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none">
                                        <option value="">{{ __('citas.select.clinic') }}</option>
                                        <template x-for="clinica in clinicas" :key="clinica.id">
                                            <option :value="clinica.id" x-text="clinica.nombre"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <!-- 4. Fecha (Visible if Doctor selected) -->
                            <div x-show="selectedDoctorId" class="mb-6" x-transition>
                                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('citas.steps.date') }}</label>
                                <div class="w-full">
                                    <input type="date" 
                                           name="fecha" 
                                           x-model="fecha" 
                                           @change="fetchSlotsIfReady" 
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none">
                                </div>
                            </div>

                            <!-- 5. Paciente -->
                            <div x-show="selectedDoctorId" class="mb-6" x-transition>
                                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('citas.steps.select_patient') }}</label>
                                <div class="relative" @click.outside="patientDropdownOpen = false">
                                    <button type="button"
                                            @click="togglePatientDropdown()"
                                            class="shadow appearance-none border border-gray-300 rounded w-full py-2 px-3 text-left text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none flex items-center justify-between bg-white"
                                            :class="{'border-green-500': selectedPatient, 'border-[#0061F5] ring-1 ring-[#0061F5]': patientDropdownOpen}">
                                        <span x-text="selectedPatient ? patientDisplayName(selectedPatient) : '{{ __('citas.placeholders.select_patient') }}'"
                                              :class="selectedPatient ? 'text-gray-800' : 'text-gray-500'"></span>
                                        <i class="fas fa-chevron-down text-gray-400 ml-3 transition-transform" :class="{'rotate-180': patientDropdownOpen}"></i>
                                    </button>
                                    <input type="hidden" name="paciente_id" x-model="selectedPatientId">

                                    <div x-show="patientDropdownOpen"
                                         x-transition
                                         class="absolute z-50 bg-white w-full border border-gray-200 rounded-lg shadow-xl mt-2 overflow-hidden">
                                        <div class="p-2 border-b border-gray-100 bg-gray-50">
                                            <input type="text"
                                                   x-model="searchPatient"
                                                   @input="filterPatients()"
                                                   @keydown.escape="patientDropdownOpen = false"
                                                   x-ref="patientSearchInput"
                                                   autocomplete="new-password"
                                                   autocorrect="off"
                                                   autocapitalize="none"
                                                   spellcheck="false"
                                                   placeholder="{{ __('citas.placeholders.search_patient') }}"
                                                   class="appearance-none border border-gray-300 rounded w-full py-2 px-3 text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none bg-white">
                                        </div>
                                        <div class="max-h-60 overflow-y-auto">
                                            <template x-for="patient in patients" :key="patient.id">
                                                <button type="button" @click="selectPatient(patient)" class="w-full p-3 hover:bg-[#E6F0FF] cursor-pointer border-b border-gray-100 text-left transition-colors last:border-b-0">
                                                    <span class="font-semibold text-gray-800" x-text="patientDisplayName(patient)"></span>
                                                    <span class="block text-xs text-gray-500" x-show="patient.email" x-text="patient.email"></span>
                                                </button>
                                            </template>
                                            <div x-show="patients.length === 0" class="p-4 text-sm text-gray-500 text-center">
                                                {{ __('citas.select.no_patients') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div x-show="selectedPatient" class="mt-2 text-green-600 font-semibold">
                                    {{ __('citas.labels.patient_selected') }} <span x-text="patientDisplayName(selectedPatient)"></span>
                                    <button type="button" @click="resetPatient" class="ml-2 text-red-500 text-sm underline">{{ __('citas.buttons.change') }}</button>
                                </div>
                            </div>

                            <!-- 6. Horarios Disponibles (Slots) -->
                            <div x-show="isLoading" class="mb-6 text-center">
                                <span class="text-[#0061F5] font-bold">{{ __('citas.slots.loading') }}</span>
                            </div>

                            <div x-show="!isLoading && totalSlots > 0" class="mb-6" x-transition>
                                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('citas.steps.available_slots') }} <span class="text-sm font-normal text-gray-500 ml-2">{{ __('citas.helpers.select_slot_hint') }}</span></label>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    
                                    <!-- Mañana -->
                                    <div x-show="slots.morning && slots.morning.length > 0" class="bg-yellow-50 rounded-lg p-4 border border-yellow-100">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-bold text-yellow-800 flex items-center">
                                                <i class="fas fa-sun mr-2"></i> {{ __('horarios.periods.morning') }}
                                            </h4>
                                            <span class="text-xs font-medium text-yellow-600 bg-yellow-100 px-2 py-1 rounded">{{ __('citas.slots.morning_range') }}</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <template x-for="slot in slots.morning" :key="slot">
                                                <button type="button" 
                                                        @click="selectedSlot = slot"
                                                        :class="{'bg-yellow-500 text-white shadow-md transform scale-105': selectedSlot === slot, 'bg-white text-gray-700 hover:bg-yellow-100 border border-yellow-200': selectedSlot !== slot}"
                                                        class="py-2 px-2 rounded text-center text-sm font-medium transition-all duration-200">
                                                    <span x-text="formatTime12h(slot)"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Tarde -->
                                    <div x-show="slots.afternoon && slots.afternoon.length > 0" class="bg-orange-50 rounded-lg p-4 border border-orange-100">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-bold text-orange-800 flex items-center">
                                                <i class="fas fa-cloud-sun mr-2"></i> {{ __('horarios.periods.afternoon') }}
                                            </h4>
                                            <span class="text-xs font-medium text-orange-600 bg-orange-100 px-2 py-1 rounded">{{ __('citas.slots.afternoon_range') }}</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <template x-for="slot in slots.afternoon" :key="slot">
                                                <button type="button" 
                                                        @click="selectedSlot = slot"
                                                        :class="{'bg-orange-500 text-white shadow-md transform scale-105': selectedSlot === slot, 'bg-white text-gray-700 hover:bg-orange-100 border border-orange-200': selectedSlot !== slot}"
                                                        class="py-2 px-2 rounded text-center text-sm font-medium transition-all duration-200">
                                                    <span x-text="formatTime12h(slot)"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- Noche -->
                                    <div x-show="slots.night && slots.night.length > 0" class="bg-indigo-50 rounded-lg p-4 border border-indigo-100">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-bold text-indigo-800 flex items-center">
                                                <i class="fas fa-moon mr-2"></i> {{ __('horarios.periods.night') }}
                                            </h4>
                                            <span class="text-xs font-medium text-indigo-600 bg-indigo-100 px-2 py-1 rounded">{{ __('citas.slots.night_range') }}</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <template x-for="slot in slots.night" :key="slot">
                                                <button type="button" 
                                                        @click="selectedSlot = slot"
                                                        :class="{'bg-indigo-500 text-white shadow-md transform scale-105': selectedSlot === slot, 'bg-white text-gray-700 hover:bg-indigo-100 border border-indigo-200': selectedSlot !== slot}"
                                                        class="py-2 px-2 rounded text-center text-sm font-medium transition-all duration-200">
                                                    <span x-text="formatTime12h(slot)"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                </div>

                                <input type="hidden" name="hora_inicio" x-model="selectedSlot">
                            </div>
                            
                            <div x-show="!isLoading && totalSlots === 0 && fecha && selectedConsultorioId" class="mb-6 text-red-500">
                                <p>{{ __('citas.slots.none_available') }}</p>
                                <template x-if="errorMessage">
                                    <p x-text="errorMessage" class="text-sm mt-1"></p>
                                </template>
                            </div>

                            <!-- 7. Motivo -->
                            <div x-show="selectedSlot" class="mb-6" x-transition>
                                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('citas.steps.reason') }}</label>
                                <textarea name="motivo" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none"></textarea>
                            </div>

                            <div class="flex items-center justify-end mt-6 space-x-4">
                                <div x-show="!isValid" class="text-sm text-red-500 font-medium mr-4">
                                    <span x-show="!selectedDoctorId">{{ __('citas.validation.missing_doctor') }}</span>
                                    <span x-show="selectedDoctorId && !selectedConsultorioId">{{ __('citas.validation.missing_office') }}</span>
                                    <span x-show="selectedDoctorId && !selectedClinicaId">{{ __('citas.validation.missing_clinic') }}</span>
                                    <span x-show="selectedDoctorId && selectedConsultorioId && !selectedSlot">{{ __('citas.validation.missing_slot') }}</span>
                                    <span x-show="selectedDoctorId && selectedConsultorioId && selectedSlot && !selectedPatientId">{{ __('citas.validation.missing_patient') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('citas.index') }}" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors">{{ __('common.buttons.cancel') }}</a>
                                    <x-primary-button
                                            x-bind:disabled="!isValid"
                                            class="disabled:opacity-50 disabled:cursor-not-allowed">
                                        {{ __('citas.buttons.save') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="fixed-doctor-json">
        {!! json_encode((isset($doctor) && $doctor) ? [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'apellido_paterno' => $doctor->apellido_paterno,
        ] : null, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    <script type="application/json" id="available-doctors-json">
        {!! json_encode($doctors->map(fn ($doctor) => [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'apellido_paterno' => $doctor->apellido_paterno,
            'apellido_materno' => $doctor->apellido_materno,
            'email' => $doctor->email,
        ])->values(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    <script type="application/json" id="available-patients-json">
        {!! json_encode($pacientes->map(fn ($paciente) => [
            'id' => $paciente->id,
            'name' => $paciente->name,
            'apellido_paterno' => $paciente->apellido_paterno,
            'apellido_materno' => $paciente->apellido_materno,
            'email' => $paciente->email,
        ])->values(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    <script type="application/json" id="citas-i18n-json">
        {!! json_encode([
            'connectionErrorSlots' => __('citas.api.connection_error_slots'),
            'am' => __('citas.time.am'),
            'pm' => __('citas.time.pm'),
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    <script>
        function appointmentForm() {
            let t = {
                connectionErrorSlots: '',
                am: '',
                pm: '',
            };

            return {
                searchDoctor: '',
                allDoctors: [],
                doctors: [],
                doctorDropdownOpen: false,
                selectedDoctor: null,
                selectedDoctorId: '',
                isDoctorFixed: false,
                
                consultorios: [],
                selectedConsultorioId: '',
                
                clinicas: [],
                selectedClinicaId: '',
                
                fecha: new Date().toLocaleDateString('en-CA'),
                
                searchPatient: '',
                allPatients: [],
                patients: [],
                patientDropdownOpen: false,
                selectedPatient: null,
                selectedPatientId: '',
                
                slots: {},
                totalSlots: 0,
                selectedSlot: '',
                isLoading: false,
                
                errorMessage: '',

                init() {
                    const i18nEl = document.getElementById('citas-i18n-json');
                    if (i18nEl && i18nEl.textContent) {
                        try {
                            const parsed = JSON.parse(i18nEl.textContent);
                            if (parsed && typeof parsed === 'object') {
                                t = { ...t, ...parsed };
                            }
                        } catch (e) {
                        }
                    }

                    const doctorsEl = document.getElementById('available-doctors-json');
                    if (doctorsEl && doctorsEl.textContent) {
                        try {
                            const parsedDoctors = JSON.parse(doctorsEl.textContent);
                            this.allDoctors = Array.isArray(parsedDoctors) ? parsedDoctors : [];
                            this.doctors = this.allDoctors;
                        } catch (e) {
                            this.allDoctors = [];
                            this.doctors = [];
                        }
                    }

                    const patientsEl = document.getElementById('available-patients-json');
                    if (patientsEl && patientsEl.textContent) {
                        try {
                            const parsedPatients = JSON.parse(patientsEl.textContent);
                            this.allPatients = Array.isArray(parsedPatients) ? parsedPatients : [];
                            this.patients = this.allPatients;
                        } catch (e) {
                            this.allPatients = [];
                            this.patients = [];
                        }
                    }

                    const fixedDoctorEl = document.getElementById('fixed-doctor-json');
                    if (!fixedDoctorEl || !fixedDoctorEl.textContent) {
                        return;
                    }

                    let fixedDoctor = null;
                    try {
                        fixedDoctor = JSON.parse(fixedDoctorEl.textContent);
                    } catch (e) {
                        fixedDoctor = null;
                    }

                    if (!fixedDoctor) {
                        return;
                    }

                    this.selectedDoctorId = fixedDoctor.id;
                    this.searchDoctor = `${fixedDoctor.name} ${fixedDoctor.apellido_paterno}`;
                    this.selectedDoctor = fixedDoctor;
                    this.isDoctorFixed = true;
                    this.loadDoctorData(fixedDoctor.id);
                },

                get isValid() {
                    return this.selectedDoctorId && 
                           this.selectedConsultorioId && 
                           this.selectedClinicaId && 
                           this.fecha && 
                           this.selectedPatientId && 
                           this.selectedSlot;
                },

                toggleDoctorDropdown() {
                    this.doctorDropdownOpen = !this.doctorDropdownOpen;
                    if (this.doctorDropdownOpen) {
                        this.searchDoctor = '';
                        this.doctors = this.allDoctors;
                        this.$nextTick(() => this.$refs.doctorSearchInput?.focus());
                    }
                },

                filterDoctors() {
                    const search = this.searchDoctor.trim().toLowerCase();
                    if (!search) {
                        this.doctors = this.allDoctors;
                        return;
                    }

                    this.doctors = this.allDoctors.filter((doctor) => {
                        return this.doctorDisplayName(doctor).toLowerCase().includes(search)
                            || (doctor.email || '').toLowerCase().includes(search);
                    });
                },

                async selectDoctor(doctor) {
                    this.selectedDoctor = doctor;
                    this.selectedDoctorId = doctor.id;
                    this.searchDoctor = '';
                    this.doctorDropdownOpen = false;
                    this.doctors = this.allDoctors;
                    
                    this.loadDoctorData(doctor.id);
                },

                doctorDisplayName(doctor) {
                    return [doctor.name, doctor.apellido_paterno, doctor.apellido_materno]
                        .filter(Boolean)
                        .join(' ');
                },

                patientDisplayName(patient) {
                    return [patient.name, patient.apellido_paterno, patient.apellido_materno]
                        .filter(Boolean)
                        .join(' ');
                },

                async loadDoctorData(doctorId) {
                    try {
                        const url = "{{ route('api.doctors.data', ['id' => 'PLACEHOLDER_ID']) }}".replace('PLACEHOLDER_ID', doctorId);
                        const response = await fetch(url);
                        const data = await response.json();
                        this.consultorios = data.consultorios || [];
                        this.clinicas = data.clinicas || [];

                        if (this.consultorios.length === 1) {
                            this.selectedConsultorioId = this.consultorios[0].id;
                        }
                        if (this.clinicas.length === 1) {
                            this.selectedClinicaId = this.clinicas[0].id;
                        }

                        this.fetchSlotsIfReady();
                    } catch (error) {
                        console.error('Error fetching doctor data:', error);
                    }
                },

                resetDoctor() {
                    this.selectedDoctor = null;
                    this.selectedDoctorId = '';
                    this.searchDoctor = '';
                    this.doctors = this.allDoctors;
                    this.consultorios = [];
                    this.clinicas = [];
                    this.slots = {};
                    this.totalSlots = 0;
                    this.selectedConsultorioId = '';
                    this.selectedClinicaId = '';
                },

                togglePatientDropdown() {
                    this.patientDropdownOpen = !this.patientDropdownOpen;
                    if (this.patientDropdownOpen) {
                        this.searchPatient = '';
                        this.patients = this.allPatients;
                        this.$nextTick(() => this.$refs.patientSearchInput?.focus());
                    }
                },

                filterPatients() {
                    const search = this.searchPatient.trim().toLowerCase();
                    if (!search) {
                        this.patients = this.allPatients;
                        return;
                    }

                    this.patients = this.allPatients.filter((patient) => {
                        return this.patientDisplayName(patient).toLowerCase().includes(search)
                            || (patient.email || '').toLowerCase().includes(search);
                    });
                },

                selectPatient(patient) {
                    this.selectedPatient = patient;
                    this.selectedPatientId = patient.id;
                    this.searchPatient = '';
                    this.patientDropdownOpen = false;
                    this.patients = this.allPatients;
                },

                resetPatient() {
                    this.selectedPatient = null;
                    this.selectedPatientId = '';
                    this.searchPatient = '';
                    this.patients = this.allPatients;
                },

                async fetchSlotsIfReady() {
                    if (this.selectedDoctorId && this.selectedConsultorioId && this.fecha) {
                        this.isLoading = true;
                        this.slots = {};
                        this.totalSlots = 0;
                        this.errorMessage = '';
                        this.selectedSlot = '';
                        
                        try {
                            const response = await fetch(`{{ route('api.slots') }}?doctor_id=${this.selectedDoctorId}&consultorio_id=${this.selectedConsultorioId}&fecha=${this.fecha}`);
                            const data = await response.json();
                            
                            if (data.total_slots > 0) {
                                this.slots = data.slots;
                                this.totalSlots = data.total_slots;
                            } else {
                                this.slots = {};
                                this.totalSlots = 0;
                                if (data.message) {
                                    this.errorMessage = data.message;
                                }
                            }
                        } catch (error) {
                            console.error('Error fetching slots:', error);
                            this.slots = {};
                            this.totalSlots = 0;
                            this.errorMessage = t.connectionErrorSlots;
                        } finally {
                            this.isLoading = false;
                        }
                    }
                },

                submitForm(event) {
                    event.target.submit();
                },

                formatTime12h(time) {
                    if (!time) return '';
                    let [hours, minutes] = time.split(':');
                    hours = parseInt(hours);
                    const ampm = hours >= 12 ? t.pm : t.am;
                    hours = hours % 12;
                    hours = hours ? hours : 12; // the hour '0' should be '12'
                    hours = hours < 10 ? '0' + hours : hours;
                    return `${hours}:${minutes} ${ampm}`;
                }
            }
        }
    </script>
</x-admin-layout>
