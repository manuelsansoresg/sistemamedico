<x-admin-layout>
    <div class="py-12" x-data="horarioManager()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ auth()->user()->hasRole('doctor') ? route('dashboard') : route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li class="inline-flex items-center">
                        <a href="{{ route('horarios.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            Horarios
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Gestionar</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="mb-6 flex justify-between items-start">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Horario de {{ $user->name }} {{ $user->apellido_paterno }}
                    </h2>
                    <p class="text-gray-600 text-sm mt-1">Consultorio: <span class="font-bold">{{ $consultorio->nombre }}</span></p>
                </div>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">¡Éxito!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('horarios.store') }}" method="POST" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="consultorio_id" value="{{ $consultorio->id }}">

                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <label for="duracion_consulta" class="block text-sm font-bold text-gray-700 mb-1">
                            Tiempo de consulta
                        </label>
                        @php
                            $duraciones = [20, 30, 45, 60];
                            $valorDuracion = old('duracion_consulta', $duracionConsulta ?? 30);
                        @endphp
                        <select
                            name="duracion_consulta"
                            id="duracion_consulta"
                            class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] text-sm"
                        >
                            @foreach($duraciones as $min)
                                <option value="{{ $min }}" {{ (int)$valorDuracion === $min ? 'selected' : '' }}>
                                    {{ $min < 60 ? $min . ' min' : ($min / 60) . ' hr' }}
                                </option>
                            @endforeach
                        </select>
                        @error('duracion_consulta')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-8">
                        @foreach([1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 0 => 'Domingo'] as $diaNum => $diaNombre)
                            <div class="border-b border-gray-100 pb-6 last:border-0 last:pb-0">
                                <div class="mb-4">
                                    <h3 class="text-lg font-bold text-gray-800">{{ $diaNombre }}</h3>
                                </div>
                                
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <!-- Mañana Section -->
                                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-100">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-bold text-yellow-800 flex items-center">
                                                <i class="fas fa-sun mr-2"></i> Mañana
                                            </h4>
                                            <span class="text-xs font-medium text-yellow-600 bg-yellow-100 px-2 py-1 rounded">07:00 - 12:00</span>
                                        </div>
                                        
                                        <div class="space-y-3">
                                            <template x-for="(slot, index) in slots[{{ $diaNum }}]" :key="index">
                                                <div x-show="slot.periodo === 'Mañana'" class="bg-white p-2 rounded border border-yellow-200 shadow-sm relative">
                                                    <div class="flex flex-col gap-2">
                                                        <div class="flex items-center justify-between">
                                                            <div class="w-full mr-2">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">Inicio</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][inicio]`" 
                                                                       x-model="slot.inicio" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-yellow-500 focus:border-yellow-500">
                                                            </div>
                                                            <div class="w-full">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">Fin</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][fin]`" 
                                                                       x-model="slot.fin" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-yellow-500 focus:border-yellow-500">
                                                            </div>
                                                        </div>
                                                        <div x-show="slot.error" class="text-xs text-red-600 font-medium" x-text="slot.error"></div>
                                                        <button type="button" @click="removeSlot({{ $diaNum }}, index)" class="text-red-500 text-xs hover:text-red-700 underline self-end">
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <button x-show="!hasSlot({{ $diaNum }}, 'Mañana')" type="button" @click="addSlot({{ $diaNum }}, 'Mañana')" class="w-full py-2 border-2 border-dashed border-yellow-300 text-yellow-700 rounded hover:bg-yellow-100 transition-colors text-sm font-medium">
                                                <i class="fas fa-plus mr-1"></i> Agregar Turno
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Tarde Section -->
                                    <div class="bg-orange-50 rounded-lg p-4 border border-orange-100">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-bold text-orange-800 flex items-center">
                                                <i class="fas fa-cloud-sun mr-2"></i> Tarde
                                            </h4>
                                            <span class="text-xs font-medium text-orange-600 bg-orange-100 px-2 py-1 rounded">12:00 - 19:00</span>
                                        </div>
                                        
                                        <div class="space-y-3">
                                            <template x-for="(slot, index) in slots[{{ $diaNum }}]" :key="index">
                                                <div x-show="slot.periodo === 'Tarde'" class="bg-white p-2 rounded border border-orange-200 shadow-sm relative">
                                                    <div class="flex flex-col gap-2">
                                                        <div class="flex items-center justify-between">
                                                            <div class="w-full mr-2">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">Inicio</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][inicio]`" 
                                                                       x-model="slot.inicio" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-orange-500 focus:border-orange-500">
                                                            </div>
                                                            <div class="w-full">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">Fin</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][fin]`" 
                                                                       x-model="slot.fin" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-orange-500 focus:border-orange-500">
                                                            </div>
                                                        </div>
                                                        <div x-show="slot.error" class="text-xs text-red-600 font-medium" x-text="slot.error"></div>
                                                        <button type="button" @click="removeSlot({{ $diaNum }}, index)" class="text-red-500 text-xs hover:text-red-700 underline self-end">
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <button x-show="!hasSlot({{ $diaNum }}, 'Tarde')" type="button" @click="addSlot({{ $diaNum }}, 'Tarde')" class="w-full py-2 border-2 border-dashed border-orange-300 text-orange-700 rounded hover:bg-orange-100 transition-colors text-sm font-medium">
                                                <i class="fas fa-plus mr-1"></i> Agregar Turno
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Noche Section -->
                                    <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-bold text-indigo-800 flex items-center">
                                                <i class="fas fa-moon mr-2"></i> Noche
                                            </h4>
                                            <span class="text-xs font-medium text-indigo-600 bg-indigo-100 px-2 py-1 rounded">19:00 - 21:00</span>
                                        </div>
                                        
                                        <div class="space-y-3">
                                            <template x-for="(slot, index) in slots[{{ $diaNum }}]" :key="index">
                                                <div x-show="slot.periodo === 'Noche'" class="bg-white p-2 rounded border border-indigo-200 shadow-sm relative">
                                                    <div class="flex flex-col gap-2">
                                                        <div class="flex items-center justify-between">
                                                            <div class="w-full mr-2">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">Inicio</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][inicio]`" 
                                                                       x-model="slot.inicio" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                            </div>
                                                            <div class="w-full">
                                                                <label class="text-[10px] text-gray-500 uppercase font-bold block mb-1">Fin</label>
                                                                <input type="time" 
                                                                       :name="`horarios[{{ $diaNum }}][${index}][fin]`" 
                                                                       x-model="slot.fin" 
                                                                       @change="validateRange(slot)"
                                                                       class="w-full rounded border-gray-300 text-sm p-1 focus:ring-indigo-500 focus:border-indigo-500">
                                                            </div>
                                                        </div>
                                                        <div x-show="slot.error" class="text-xs text-red-600 font-medium" x-text="slot.error"></div>
                                                        <button type="button" @click="removeSlot({{ $diaNum }}, index)" class="text-red-500 text-xs hover:text-red-700 underline self-end">
                                                            Eliminar
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <button x-show="!hasSlot({{ $diaNum }}, 'Noche')" type="button" @click="addSlot({{ $diaNum }}, 'Noche')" class="w-full py-2 border-2 border-dashed border-indigo-300 text-indigo-700 rounded hover:bg-indigo-100 transition-colors text-sm font-medium">
                                                <i class="fas fa-plus mr-1"></i> Agregar Turno
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <a href="{{ route('horarios.index') }}" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors mr-2">Cancelar</a>
                    <x-primary-button>
                        Guardar Horarios
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function horarioManager() {
            return {
                slots: {
                    0: [], 1: [], 2: [], 3: [], 4: [], 5: [], 6: []
                },
                init() {
                    // Populate existing slots from backend
                    const existingHorarios = @json($horarios);
                    
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

                    if (periodo === 'Mañana') {
                        inicio = '08:00';
                        fin = '12:00';
                    } else if (periodo === 'Tarde') {
                        inicio = '13:00';
                        fin = '17:00';
                    } else if (periodo === 'Noche') {
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
                    if (hour < 12) return 'Mañana';
                    if (hour < 19) return 'Tarde';
                    return 'Noche';
                },
                hasSlot(dia, periodo) {
                    return this.slots[dia].some(s => s.periodo === periodo);
                },
                validateRange(slot) {
                    // Strict limits per period
                    let minTime, maxTime, periodName;

                    if (slot.periodo === 'Mañana') {
                        minTime = '07:00';
                        maxTime = '12:00';
                        periodName = 'la Mañana';
                    } else if (slot.periodo === 'Tarde') {
                        minTime = '12:00';
                        maxTime = '19:00';
                        periodName = 'la Tarde';
                    } else if (slot.periodo === 'Noche') {
                        minTime = '19:00';
                        maxTime = '21:00';
                        periodName = 'la Noche';
                    } else {
                        // Fallback global limits
                        minTime = '07:00';
                        maxTime = '21:00';
                        periodName = 'el día';
                    }
                    
                    slot.error = null;

                    // Enforce Min/Max for the specific period
                    if (slot.inicio < minTime) {
                        slot.inicio = minTime;
                        slot.error = `Horario de inicio ajustado al mínimo de ${periodName} (${minTime})`;
                    }
                    if (slot.inicio > maxTime) {
                        slot.inicio = maxTime;
                        slot.error = `Horario de inicio no puede exceder el límite de ${periodName} (${maxTime})`;
                    }

                    if (slot.fin > maxTime) {
                        slot.fin = maxTime;
                        slot.error = `Horario de fin ajustado al máximo de ${periodName} (${maxTime})`;
                    }
                    if (slot.fin < minTime) {
                        slot.fin = minTime;
                        slot.error = `Horario de fin no puede ser menor al inicio de ${periodName} (${minTime})`;
                    }
                    
                    // Enforce Start < End
                    if (slot.inicio >= slot.fin) {
                        slot.error = 'La hora de inicio debe ser anterior a la hora de fin';
                        // Optional: Reset end time to valid range or just show error
                    }
                }
            }
        }
    </script>
</x-admin-layout>
