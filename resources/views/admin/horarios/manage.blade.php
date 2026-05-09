<x-admin-layout>
    <div class="py-12" x-data="horarioManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ auth()->user()->hasRole('doctor') ? route('dashboard') : route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li class="inline-flex items-center">
                        <a href="{{ route('horarios.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            {{ __('horarios.breadcrumbs.index') }}
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('horarios.breadcrumbs.manage') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <form action="{{ route('horarios.store') }}" method="POST" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="consultorio_id" value="{{ $consultorio->id }}">

                <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-6 pt-6">
                    <div>
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            {{ __('horarios.titles.schedule_of', ['doctor' => $user->name . ' ' . $user->apellido_paterno]) }}
                        </h2>
                        <p class="text-gray-600 text-sm mt-1">
                            {{ __('horarios.fields.office') }}: <span class="font-bold">{{ $consultorio->nombre }}</span>
                        </p>
                    </div>

                    @if($user->consultorios->count() > 1)
                        <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3">
                            <p class="text-xs font-semibold text-blue-700 mb-2">{{ __('horarios.sections.quick_shortcut') }}</p>
                            <div class="flex flex-col md:flex-row md:items-center md:space-x-3 gap-2">
                                <span class="text-xs text-blue-800">{{ __('horarios.sections.copy_from_other_office') }}</span>
                                <select
                                    name="copiar_desde_consultorio_id"
                                    class="mt-1 md:mt-0 block w-full md:w-56 rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] text-xs"
                                    onchange="if(this.value){ this.form.submit(); }"
                                >
                                    <option value="">{{ __('horarios.sections.select_origin_office') }}</option>
                                    @foreach($user->consultorios as $otroConsultorio)
                                        @if($otroConsultorio->id !== $consultorio->id)
                                            <option value="{{ $otroConsultorio->id }}">
                                                {{ $otroConsultorio->nombre }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <p class="text-[11px] text-blue-500 mt-1">
                                {{ __('horarios.sections.overwrite_warning') }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="px-6 pb-6 text-gray-900">
                    <div class="mb-6">
                        <label for="duracion_consulta" class="block text-sm font-bold text-gray-700 mb-1">
                            {{ __('horarios.fields.consultation_time') }}
                        </label>
                        @php
                            $duraciones = [15, 20, 30, 45, 60];
                            $valorDuracion = old('duracion_consulta', $duracionConsulta ?? 30);
                        @endphp
                        <select
                            name="duracion_consulta"
                            id="duracion_consulta"
                            class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] text-sm"
                        >
                            @foreach($duraciones as $min)
                                <option value="{{ $min }}" {{ (int)$valorDuracion === $min ? 'selected' : '' }}>
                                    {{ $min < 60 ? $min . ' ' . __('horarios.units.min') : ($min / 60) . ' ' . __('horarios.units.hr') }}
                                </option>
                            @endforeach
                        </select>
                        @error('duracion_consulta')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-8">
                        @php
                            $dias = [
                                1 => __('horarios.days.monday'),
                                2 => __('horarios.days.tuesday'),
                                3 => __('horarios.days.wednesday'),
                                4 => __('horarios.days.thursday'),
                                5 => __('horarios.days.friday'),
                                6 => __('horarios.days.saturday'),
                                0 => __('horarios.days.sunday'),
                            ];
                        @endphp
                        @foreach($dias as $diaNum => $diaNombre)
                            <div class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                                <div class="mb-4">
                                    <h3 class="text-lg font-bold text-gray-800">{{ $diaNombre }}</h3>
                                </div>
                                
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <!-- Mañana Section -->
                                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-100">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-bold text-yellow-800 flex items-center">
                                                <i class="fas fa-sun mr-2"></i> {{ __('horarios.periods.morning') }}
                                            </h4>
                                            <span class="text-xs font-medium text-yellow-600 bg-yellow-100 px-2 py-1 rounded">07:00 - 12:00</span>
                                        </div>
                                        
                                        <div class="space-y-3">
                                            <template x-for="(slot, index) in slots[{{ $diaNum }}]" :key="index">
                                                <div x-show="slot.periodo === 'morning'" class="bg-white p-2 rounded border border-yellow-200 shadow-sm relative">
                                                    <div class="flex flex-col gap-2">
                                                        <div class="flex items-center justify-between">
                                                            <div class="w-full mr-2">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">{{ __('horarios.fields.start') }}</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][inicio]`" 
                                                                       x-model="slot.inicio" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-yellow-500 focus:border-yellow-500">
                                                            </div>
                                                            <div class="w-full">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">{{ __('horarios.fields.end') }}</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][fin]`" 
                                                                       x-model="slot.fin" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-yellow-500 focus:border-yellow-500">
                                                            </div>
                                                        </div>
                                                        <div x-show="slot.error" class="text-xs text-red-600 font-medium" x-text="slot.error"></div>
                                                        <button type="button" @click="removeSlot({{ $diaNum }}, index)" class="text-red-500 text-xs hover:text-red-700 underline self-end">
                                                            {{ __('horarios.buttons.remove') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <button x-show="!hasSlot({{ $diaNum }}, 'morning')" type="button" @click="addSlot({{ $diaNum }}, 'morning')" class="w-full py-2 border-2 border-dashed border-yellow-300 text-yellow-700 rounded hover:bg-yellow-100 transition-colors text-sm font-medium">
                                                <i class="fas fa-plus mr-1"></i> {{ __('horarios.buttons.add_slot') }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Tarde Section -->
                                    <div class="bg-orange-50 rounded-lg p-4 border border-orange-100">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-bold text-orange-800 flex items-center">
                                                <i class="fas fa-cloud-sun mr-2"></i> {{ __('horarios.periods.afternoon') }}
                                            </h4>
                                            <span class="text-xs font-medium text-orange-600 bg-orange-100 px-2 py-1 rounded">12:00 - 19:00</span>
                                        </div>
                                        
                                        <div class="space-y-3">
                                            <template x-for="(slot, index) in slots[{{ $diaNum }}]" :key="index">
                                                <div x-show="slot.periodo === 'afternoon'" class="bg-white p-2 rounded border border-orange-200 shadow-sm relative">
                                                    <div class="flex flex-col gap-2">
                                                        <div class="flex items-center justify-between">
                                                            <div class="w-full mr-2">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">{{ __('horarios.fields.start') }}</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][inicio]`" 
                                                                       x-model="slot.inicio" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-orange-500 focus:border-orange-500">
                                                            </div>
                                                            <div class="w-full">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">{{ __('horarios.fields.end') }}</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][fin]`" 
                                                                       x-model="slot.fin" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-orange-500 focus:border-orange-500">
                                                            </div>
                                                        </div>
                                                        <div x-show="slot.error" class="text-xs text-red-600 font-medium" x-text="slot.error"></div>
                                                        <button type="button" @click="removeSlot({{ $diaNum }}, index)" class="text-red-500 text-xs hover:text-red-700 underline self-end">
                                                            {{ __('horarios.buttons.remove') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <button x-show="!hasSlot({{ $diaNum }}, 'afternoon')" type="button" @click="addSlot({{ $diaNum }}, 'afternoon')" class="w-full py-2 border-2 border-dashed border-orange-300 text-orange-700 rounded hover:bg-orange-100 transition-colors text-sm font-medium">
                                                <i class="fas fa-plus mr-1"></i> {{ __('horarios.buttons.add_slot') }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Noche Section -->
                                    <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-bold text-indigo-800 flex items-center">
                                                <i class="fas fa-moon mr-2"></i> {{ __('horarios.periods.night') }}
                                            </h4>
                                            <span class="text-xs font-medium text-indigo-600 bg-indigo-100 px-2 py-1 rounded">19:00 - 21:00</span>
                                        </div>
                                        
                                        <div class="space-y-3">
                                            <template x-for="(slot, index) in slots[{{ $diaNum }}]" :key="index">
                                                <div x-show="slot.periodo === 'night'" class="bg-white p-2 rounded border border-indigo-200 shadow-sm relative">
                                                    <div class="flex flex-col gap-2">
                                                        <div class="flex items-center justify-between">
                                                            <div class="w-full mr-2">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">{{ __('horarios.fields.start') }}</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][inicio]`" 
                                                                       x-model="slot.inicio" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                            </div>
                                                            <div class="w-full">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">{{ __('horarios.fields.end') }}</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][fin]`" 
                                                                       x-model="slot.fin" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                            </div>
                                                        </div>
                                                        <div x-show="slot.error" class="text-xs text-red-600 font-medium" x-text="slot.error"></div>
                                                        <button type="button" @click="removeSlot({{ $diaNum }}, index)" class="text-red-500 text-xs hover:text-red-700 underline self-end">
                                                            {{ __('horarios.buttons.remove') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <button x-show="!hasSlot({{ $diaNum }}, 'night')" type="button" @click="addSlot({{ $diaNum }}, 'night')" class="w-full py-2 border-2 border-dashed border-indigo-300 text-indigo-700 rounded hover:bg-indigo-100 transition-colors text-sm font-medium">
                                                <i class="fas fa-plus mr-1"></i> {{ __('horarios.buttons.add_slot') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <a href="{{ route('horarios.index') }}" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors mr-2">{{ __('common.buttons.cancel') }}</a>
                    <x-primary-button>
                        {{ __('horarios.buttons.save_schedules') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script type="application/json" id="horarios-existing">{!! json_encode($horarios) !!}</script>
    <script type="application/json" id="horarios-i18n">{!! json_encode([
        'periods' => [
            'morning_article' => __('horarios.periods.morning_article'),
            'afternoon_article' => __('horarios.periods.afternoon_article'),
            'night_article' => __('horarios.periods.night_article'),
            'day_article' => __('horarios.periods.day_article'),
        ],
        'validation' => [
            'start_adjusted_min' => __('horarios.validation.start_adjusted_min'),
            'start_exceeds_max' => __('horarios.validation.start_exceeds_max'),
            'end_adjusted_max' => __('horarios.validation.end_adjusted_max'),
            'end_before_min' => __('horarios.validation.end_before_min'),
            'start_before_end' => __('horarios.validation.start_before_end'),
        ],
    ]) !!}</script>

    <script>
        function horarioManager() {
            return {
                i18n: null,
                slots: {
                    0: [], 1: [], 2: [], 3: [], 4: [], 5: [], 6: []
                },
                init() {
                    // Populate existing slots from backend
                    this.i18n = JSON.parse(document.getElementById('horarios-i18n')?.textContent || '{}');
                    const existingHorarios = JSON.parse(document.getElementById('horarios-existing')?.textContent || '{}');
                    
                    // Iterate through existing horarios and populate slots
                    Object.keys(existingHorarios).forEach(dia => {
                        this.slots[dia] = existingHorarios[dia].map(h => ({
                            inicio: h.hora_inicio.substring(0, 5), // Format HH:MM
                            fin: h.hora_fin.substring(0, 5),
                            periodo: this.getTurno(h.hora_inicio.substring(0, 5)), // Set explicit period
                            error: null
                        }));
                    });
                },
                addSlot(dia, periodo) {
                    // Check if a slot for this period already exists
                    if (this.slots[dia].some(s => s.periodo === periodo)) {
                        return; // Prevent duplicate slots per period
                    }

                    let inicio = '09:00';
                    let fin = '13:00';

                    if (periodo === 'morning') {
                        inicio = '08:00';
                        fin = '12:00';
                    } else if (periodo === 'afternoon') {
                        inicio = '13:00';
                        fin = '17:00';
                    } else if (periodo === 'night') {
                        inicio = '19:00';
                        fin = '21:00';
                    }
                    this.slots[dia].push({ inicio: inicio, fin: fin, periodo: periodo, error: null });
                },
                removeSlot(dia, index) {
                    this.slots[dia].splice(index, 1);
                },
                getTurno(time) {
                    if (!time) return '';
                    const hour = parseInt(time.split(':')[0]);
                    if (hour < 12) return 'morning';
                    if (hour < 19) return 'afternoon';
                    return 'night';
                },
                hasSlot(dia, periodo) {
                    return this.slots[dia].some(s => s.periodo === periodo);
                },
                validateRange(slot) {
                    const periodI18n = {
                        morning: {
                            label: this.i18n?.periods?.morning_article || '',
                            min: '07:00',
                            max: '12:00',
                        },
                        afternoon: {
                            label: this.i18n?.periods?.afternoon_article || '',
                            min: '12:00',
                            max: '19:00',
                        },
                        night: {
                            label: this.i18n?.periods?.night_article || '',
                            min: '19:00',
                            max: '21:00',
                        },
                        day: {
                            label: this.i18n?.periods?.day_article || '',
                            min: '07:00',
                            max: '21:00',
                        },
                    };

                    const messages = {
                        startAdjustedMin: this.i18n?.validation?.start_adjusted_min || '',
                        startExceedsMax: this.i18n?.validation?.start_exceeds_max || '',
                        endAdjustedMax: this.i18n?.validation?.end_adjusted_max || '',
                        endBeforeMin: this.i18n?.validation?.end_before_min || '',
                        startBeforeEnd: this.i18n?.validation?.start_before_end || '',
                    };

                    // Strict limits per period
                    let minTime, maxTime, periodName;

                    const conf = periodI18n[slot.periodo] || periodI18n.day;
                    minTime = conf.min;
                    maxTime = conf.max;
                    periodName = conf.label;
                    
                    slot.error = null;

                    // Enforce Min/Max for the specific period
                    if (slot.inicio < minTime) {
                        slot.inicio = minTime;
                        slot.error = messages.startAdjustedMin.replace(':period', periodName).replace(':time', minTime);
                    }
                    if (slot.inicio > maxTime) {
                        slot.inicio = maxTime;
                        slot.error = messages.startExceedsMax.replace(':period', periodName).replace(':time', maxTime);
                    }

                    if (slot.fin > maxTime) {
                        slot.fin = maxTime;
                        slot.error = messages.endAdjustedMax.replace(':period', periodName).replace(':time', maxTime);
                    }
                    if (slot.fin < minTime) {
                        slot.fin = minTime;
                        slot.error = messages.endBeforeMin.replace(':period', periodName).replace(':time', minTime);
                    }
                    
                    // Enforce Start < End
                    if (slot.inicio >= slot.fin) {
                        slot.error = messages.startBeforeEnd;
                        // Optional: Reset end time to valid range or just show error
                    }
                }
            }
        }
    </script>
</x-admin-layout>
