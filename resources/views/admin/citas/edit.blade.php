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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('citas.breadcrumbs.edit') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-6">{{ __('citas.headings.edit') }}</h2>

                    <div x-data="appointmentForm()" x-init="init()" class="space-y-6">
                        
                        <!-- Error Messages -->
                        <template x-if="errorMessage">
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline" x-text="errorMessage"></span>
                            </div>
                        </template>

                        <form action="{{ route('citas.update', $cita->id) }}" method="POST" @submit.prevent="submitForm">
                            @csrf
                            @method('PUT')
                            
                            <!-- 1. Seleccionar Doctor -->
                            <div class="mb-6">
                                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('citas.steps.select_doctor') }}</label>
                                
                                <div x-show="isDoctorFixed" class="p-4 bg-gray-50 border border-gray-200 rounded-lg flex items-center mb-2">
                                    <div class="bg-[#E6F0FF] p-2 rounded-full mr-3">
                                        <i class="fas fa-user-md text-[#0061F5]"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800" x-text="selectedDoctor ? (selectedDoctor.name + ' ' + selectedDoctor.apellido_paterno) : ''"></p>
                                        <p class="text-sm text-gray-500">{{ __('citas.helpers.doctor_locked') }}</p>
                                    </div>
                                    <input type="hidden" name="doctor_id" x-model="selectedDoctorId">
                                </div>

                                <div class="relative" x-show="!isDoctorFixed">
                                    <input type="text" 
                                           x-model="searchDoctor" 
                                           @input.debounce.300ms="findDoctors()"
                                           placeholder="{{ __('citas.placeholders.search_doctor') }}" 
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none"
                                           :class="{'border-green-500': selectedDoctor}"
                                    >
                                    <input type="hidden" name="doctor_id" x-model="selectedDoctorId">
                                    
                                    <!-- Results Dropdown -->
                                    <div x-show="doctors.length > 0 && !selectedDoctor" class="absolute z-10 bg-white w-full border rounded shadow-lg mt-1 max-h-60 overflow-y-auto">
                                        <template x-for="doctor in doctors" :key="doctor.id">
                                            <div @click="selectDoctor(doctor)" class="p-2 hover:bg-gray-100 cursor-pointer border-b">
                                                <span x-text="doctor.name + ' ' + doctor.apellido_paterno"></span>
                                            </div>
                                        </template>
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
                                <label class="block text-gray-700 text-sm font-bold mb-2">5. Seleccionar Paciente</label>
                                <div class="relative">
                                    <input type="text" 
                                           x-model="searchPatient" 
                                           @input.debounce.300ms="findPatients()"
                                           placeholder="Buscar paciente por nombre..." 
                                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none"
                                           :class="{'border-green-500': selectedPatient}"
                                    >
                                    <input type="hidden" name="paciente_id" x-model="selectedPatientId">
                                    
                                    <!-- Results Dropdown -->
                                    <div x-show="patients.length > 0 && !selectedPatient" class="absolute z-10 bg-white w-full border rounded shadow-lg mt-1 max-h-60 overflow-y-auto">
                                        <template x-for="patient in patients" :key="patient.id">
                                            <div @click="selectPatient(patient)" class="p-2 hover:bg-gray-100 cursor-pointer border-b">
                                                <span x-text="patient.name + ' ' + patient.apellido_paterno"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div x-show="selectedPatient" class="mt-2 text-green-600 font-semibold">
                                    {{ __('citas.labels.patient_selected') }} <span x-text="selectedPatient.name + ' ' + selectedPatient.apellido_paterno"></span>
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
                                <textarea name="motivo" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none">{{ $cita->motivo }}</textarea>
                            </div>
                            
                            <!-- Estado (Only for Edit) -->
                            <div x-show="selectedSlot" class="mb-6" x-transition>
                                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('citas.fields.status') }}</label>
                                <select name="estado" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:border-[#0061F5] focus:ring-[#0061F5] focus:outline-none">
                                    <option value="pendiente" {{ $cita->estado == 'pendiente' ? 'selected' : '' }}>{{ __('citas.statuses.pending') }}</option>
                                    <option value="confirmada" {{ $cita->estado == 'confirmada' ? 'selected' : '' }}>{{ __('citas.statuses.confirmed') }}</option>
                                    <option value="cancelada" {{ $cita->estado == 'cancelada' ? 'selected' : '' }}>{{ __('citas.statuses.canceled') }}</option>
                                    <option value="completada" {{ $cita->estado == 'completada' ? 'selected' : '' }}>{{ __('citas.statuses.completed') }}</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-end mt-6 space-x-4">
                                <div x-show="!isValid" class="text-sm text-red-500 font-medium mr-4">
                                    <span x-show="!selectedDoctorId">{{ __('citas.validation.missing_doctor') }}</span>
                                    <span x-show="selectedDoctorId && !selectedConsultorioId">{{ __('citas.validation.missing_office') }}</span>
                                    <span x-show="selectedDoctorId && !selectedClinicaId">{{ __('citas.validation.missing_clinic') }}</span>
                                    <span x-show="selectedDoctorId && selectedConsultorioId && !selectedSlot">{{ __('citas.validation.missing_slot') }}</span>
                                    <span x-show="selectedDoctorId && selectedConsultorioId && selectedSlot && !selectedPatientId">{{ __('citas.validation.missing_patient') }}</span>
                                </div>
                                <div>
                                    <x-primary-button 
                                            x-bind:disabled="!isValid"
                                            class="disabled:opacity-50 disabled:cursor-not-allowed">
                                        {{ __('common.buttons.update') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="citas-i18n-json">
        {!! json_encode([
            'connectionErrorSlots' => __('citas.api.connection_error_slots'),
            'am' => __('citas.time.am'),
            'pm' => __('citas.time.pm'),
            'loadSlotsError' => __('citas.api.error_generic'),
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    <script type="application/json" id="cita-edit-json">
        {!! json_encode([
            'doctorId' => $cita->doctor_id,
            'consultorioId' => $cita->consultorio_id,
            'clinicaId' => $cita->clinica_id,
            'fecha' => optional($cita->fecha)->format('Y-m-d'),
            'pacienteId' => $cita->paciente_id,
            'horaInicio' => optional($cita->hora_inicio)->format('H:i'),
            'isDoctorFixed' => auth()->user()->hasRole('doctor'),
            'citaId' => $cita->id,
            'doctor' => [
                'id' => $cita->doctor_id,
                'name' => $cita->doctor->name ?? '',
                'apellido_paterno' => $cita->doctor->apellido_paterno ?? '',
            ],
            'patient' => [
                'id' => $cita->paciente_id,
                'name' => $cita->paciente->name ?? '',
                'apellido_paterno' => $cita->paciente->apellido_paterno ?? '',
            ],
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>

    <script>
        function appointmentForm() {
            let t = {
                connectionErrorSlots: '',
                am: '',
                pm: '',
                loadSlotsError: '',
            };

            return {
                searchDoctor: '',
                doctors: [],
                selectedDoctor: null,
                selectedDoctorId: '',
                isDoctorFixed: false,
                
                consultorios: [],
                selectedConsultorioId: '',
                
                clinicas: [],
                selectedClinicaId: '',
                
                fecha: '',
                
                searchPatient: '',
                patients: [],
                selectedPatient: null,
                selectedPatientId: '',
                
                slots: {},
                totalSlots: 0,
                selectedSlot: '',
                isLoading: false,
                
                errorMessage: '',
                citaId: '',

                get isValid() {
                    return this.selectedDoctorId && 
                           this.selectedConsultorioId && 
                           this.selectedClinicaId && 
                           this.fecha && 
                           this.selectedPatientId && 
                           this.selectedSlot;
                },

                async init() {
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

                    const citaEl = document.getElementById('cita-edit-json');
                    if (citaEl && citaEl.textContent) {
                        try {
                            const parsed = JSON.parse(citaEl.textContent);
                            if (parsed && typeof parsed === 'object') {
                                this.selectedDoctorId = parsed.doctorId || '';
                                this.selectedConsultorioId = parsed.consultorioId || '';
                                this.selectedClinicaId = parsed.clinicaId || '';
                                this.fecha = parsed.fecha || '';
                                this.selectedPatientId = parsed.pacienteId || '';
                                this.selectedSlot = parsed.horaInicio || '';
                                this.isDoctorFixed = !!parsed.isDoctorFixed;
                                this.citaId = parsed.citaId || '';

                                if (parsed.doctor) {
                                    this.selectedDoctor = parsed.doctor;
                                }
                                if (parsed.patient) {
                                    this.selectedPatient = parsed.patient;
                                }
                            }
                        } catch (e) {
                        }
                    }
                    
                    // Load Doctor Data (Consultorios/Clinicas)
                    await this.loadDoctorData(this.selectedDoctorId);
                    
                    // Fetch Slots
                    await this.fetchSlotsIfReady();
                },

                async findDoctors() {
                    if (this.searchDoctor.length < 2) {
                        this.doctors = [];
                        return;
                    }
                    try {
                        const response = await fetch(`{{ route('api.doctors.search') }}?q=${this.searchDoctor}`);
                        this.doctors = await response.json();
                    } catch (error) {
                        console.error('Error fetching doctors:', error);
                    }
                },

                async selectDoctor(doctor) {
                    this.selectedDoctor = doctor;
                    this.selectedDoctorId = doctor.id;
                    this.searchDoctor = ''; 
                    this.doctors = [];
                    
                    this.loadDoctorData(doctor.id);
                },
                
                async loadDoctorData(doctorId) {
                     try {
                        const url = "{{ route('api.doctors.data', ['id' => 'PLACEHOLDER_ID']) }}".replace('PLACEHOLDER_ID', doctorId);
                        const response = await fetch(url);
                        const data = await response.json();
                        
                        this.consultorios = data.consultorios;
                        this.clinicas = data.clinicas;
                        
                        // If we are initializing, we already have ids. 
                        // If user changed doctor, we might need to reset.
                        // But for simplicity let's leave it.
                    } catch (error) {
                        console.error('Error fetching doctor data:', error);
                    }
                },

                resetDoctor() {
                    this.selectedDoctor = null;
                    this.selectedDoctorId = '';
                    this.consultorios = [];
                    this.clinicas = [];
                    this.selectedConsultorioId = '';
                    this.selectedClinicaId = '';
                    this.slots = {};
                    this.totalSlots = 0;
                    this.selectedSlot = '';
                },

                async fetchSlotsIfReady() {
                    if (!this.selectedDoctorId || !this.selectedConsultorioId || !this.fecha) return;

                    this.isLoading = true;
                    this.slots = {};
                    this.totalSlots = 0;
                    this.errorMessage = '';

                    try {
                        // Pass except_cita_id to allow selecting the current slot
                        const response = await fetch(`{{ route('api.slots') }}?doctor_id=${this.selectedDoctorId}&consultorio_id=${this.selectedConsultorioId}&fecha=${this.fecha}&except_cita_id=${this.citaId}`);
                        const data = await response.json();
                        
                        if (data.slots && (data.slots.morning || data.slots.afternoon || data.slots.night)) {
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
                        this.errorMessage = t.loadSlotsError || t.connectionErrorSlots;
                    } finally {
                        this.isLoading = false;
                    }
                },
                
                formatTime12h(time24) {
                    const [hours, minutes] = time24.split(':');
                    let h = parseInt(hours, 10);
                    const ampm = h >= 12 ? t.pm : t.am;
                    h = h % 12;
                    h = h ? h : 12; // the hour '0' should be '12'
                    return `${h}:${minutes} ${ampm}`;
                },

                async findPatients() {
                    if (this.searchPatient.length < 2) {
                        this.patients = [];
                        return;
                    }
                    try {
                        const response = await fetch(`{{ route('api.patients.search') }}?q=${this.searchPatient}`);
                        this.patients = await response.json();
                    } catch (error) {
                        console.error('Error fetching patients:', error);
                    }
                },

                selectPatient(patient) {
                    this.selectedPatient = patient;
                    this.selectedPatientId = patient.id;
                    this.searchPatient = '';
                    this.patients = [];
                },

                resetPatient() {
                    this.selectedPatient = null;
                    this.selectedPatientId = '';
                },

                submitForm(e) {
                    if (this.isValid) {
                        e.target.submit();
                    }
                }
            }
        }
    </script>
</x-admin-layout>
