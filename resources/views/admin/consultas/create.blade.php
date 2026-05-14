<x-admin-layout>
    <div class="py-12" x-data="consultaHandler()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Breadcrumbs -->
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('citas.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">Citas</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Nueva Consulta</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Patient Header Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-[#0061F5]">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-start gap-4">
                            <img src="{{ $paciente->profile_photo_url }}" alt="Foto del paciente" class="h-14 w-14 rounded-full object-cover border border-gray-200">
                            <div class="min-w-0">
                                <h2 class="text-2xl font-bold text-gray-800">
                                    {{ $paciente->name }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                                </h2>
                                <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-600">
                                    <span class="flex items-center"><i class="fas fa-birthday-cake mr-2 text-[#0061F5]"></i> {{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->format('d/m/Y') : 'N/A' }}</span>
                                    <span class="flex items-center"><i class="fas fa-user-clock mr-2 text-[#0061F5]"></i> {{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->age . ' años' : 'N/A' }}</span>
                                    <span class="flex items-center"><i class="fas fa-venus-mars mr-2 text-[#0061F5]"></i> {{ $paciente->sexo == 'M' ? 'Masculino' : 'Femenino' }}</span>
                                    <span class="flex items-center"><i class="fas fa-clinic-medical mr-2 text-[#0061F5]"></i> <span class="text-gray-500 mr-1">Consultorio:</span> {{ $cita->consultorio->nombre }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end gap-2">
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">EN CONSULTA</span>
                            @if(auth()->user()->hasRole('doctor'))
                                <button type="button" @click="toggleEmpatia()" class="inline-flex items-center px-3 py-1 bg-white text-[#0061F5] text-xs font-bold rounded-full border border-[#0061F5] hover:bg-blue-50 transition-colors shadow-sm">
                                    <i class="fas fa-hand-holding-heart mr-1"></i>
                                    Empatía
                                </button>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500">{{ now()->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasRole('doctor'))
                <div x-show="empathyOpen" style="display: none;" class="fixed inset-0 z-50" aria-labelledby="empathy-title" role="dialog" aria-modal="true">
                    <div x-show="empathyOpen"
                         x-transition:enter="ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute inset-0 bg-gray-500 bg-opacity-40"></div>

                    <div class="absolute top-6 right-6 w-full max-w-md">
                        <div x-show="empathyOpen"
                             x-transition:enter="ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             @click.away="closeEmpatia()"
                             class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                                <div>
                                    <h3 id="empathy-title" class="text-sm font-extrabold text-gray-800 tracking-wide uppercase">Empatía</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">Notas personales para mejorar el vínculo.</p>
                                </div>
                                <button type="button" @click="closeEmpatia()" class="text-gray-400 hover:text-gray-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <div class="p-5 space-y-4">
                                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 sticky top-0 z-10">
                                    <label class="block text-xs font-semibold text-gray-500 mb-2">Nueva nota</label>
                                    <textarea x-model="empathyNewContent" rows="2" class="shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md resize-none" placeholder="Ej. Le gusta el béisbol, viaja a España pronto..."></textarea>
                                    <div class="flex items-center justify-between mt-3">
                                        <p class="text-xs text-red-600" x-text="empathyError" x-show="empathyError"></p>
                                        <button type="button" @click="saveEmpathyNote()" class="inline-flex items-center px-4 py-1.5 bg-[#0061F5] text-white text-xs font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                            <i class="fas fa-save mr-2"></i>
                                            Guardar nota
                                        </button>
                                    </div>
                                </div>

                                <div class="bg-white rounded-xl border border-gray-100 shadow-sm flex flex-col max-h-[400px]">
                                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white rounded-t-xl z-10">
                                        <h4 class="text-xs font-extrabold text-gray-700 tracking-wide uppercase flex items-center gap-2">
                                            Histórico
                                            <span x-show="empathyLoading" class="text-gray-400"><i class="fas fa-spinner fa-spin"></i></span>
                                        </h4>
                                        <div class="flex items-center gap-2">
                                            <div class="relative">
                                                <input type="text" x-model="empathySearch" @input.debounce.500ms="fetchEmpathyNotes(true)" placeholder="Buscar..." class="w-32 sm:w-40 text-xs border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5] pl-7 py-1">
                                                <i class="fas fa-search absolute left-2.5 top-1.5 text-gray-400 text-xs"></i>
                                            </div>
                                            <button type="button" @click="fetchEmpathyNotes(true)" class="text-xs font-semibold text-[#0061F5] hover:text-[#004499]">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="p-4 overflow-y-auto flex-1">
                                        <div x-show="!empathyLoading && empathyNotes.length === 0" class="text-sm text-gray-500 italic text-center py-4">
                                            Aún no hay notas de empatía o no hay coincidencias.
                                        </div>

                                        <div class="space-y-4" x-show="empathyNotes.length > 0">
                                            <template x-for="(note, index) in empathyNotes" :key="note.id">
                                                <div>
                                                    <template x-if="index === 0 || note.month_year !== empathyNotes[index - 1].month_year">
                                                        <div class="flex items-center my-3">
                                                            <div class="flex-grow border-t border-gray-200"></div>
                                                            <span class="mx-3 text-[10px] font-bold text-gray-400 uppercase tracking-wider" x-text="note.month_year"></span>
                                                            <div class="flex-grow border-t border-gray-200"></div>
                                                        </div>
                                                    </template>

                                                    <div class="border border-gray-100 rounded-lg p-2.5 bg-gray-50/50 hover:bg-white transition-colors group">
                                                        <div class="flex items-start justify-between gap-2">
                                                            <div class="text-[11px] text-gray-400 font-medium flex items-center gap-1" x-text="note.created_at"></div>
                                                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                <button type="button" class="text-gray-400 hover:text-[#0061F5] p-1" @click="startEdit(note)" title="Editar">
                                                                    <i class="fas fa-pen text-[10px]"></i>
                                                                </button>
                                                                <button type="button" class="text-gray-400 hover:text-red-600 p-1" @click="deleteEmpathyNote(note.id)" title="Borrar">
                                                                    <i class="fas fa-trash text-[10px]"></i>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="mt-1" x-show="empathyEditingId !== note.id">
                                                            <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed" x-text="note.content"></p>
                                                        </div>

                                                        <div class="mt-2" x-show="empathyEditingId === note.id" style="display: none;">
                                                            <textarea x-model="empathyEditContent" rows="2" class="shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full text-sm border-gray-300 rounded-md"></textarea>
                                                            <div class="mt-2 flex items-center justify-end gap-2">
                                                                <button type="button" class="px-2.5 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded hover:bg-gray-200" @click="cancelEdit()">
                                                                    Cancelar
                                                                </button>
                                                                <button type="button" class="px-2.5 py-1.5 text-xs font-bold text-white bg-[#0061F5] rounded hover:bg-[#0051CC]" @click="updateEmpathyNote(note.id)">
                                                                    Guardar
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            
                                            <div class="pt-2 pb-1 text-center" x-show="empathyHasMore">
                                                <button type="button" @click="loadMoreEmpathyNotes()" class="text-xs font-semibold text-[#0061F5] hover:text-[#004499] bg-blue-50 hover:bg-blue-100 px-4 py-1.5 rounded-full transition-colors">
                                                    <i class="fas fa-chevron-down mr-1"></i> Ver notas anteriores
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Health Metrics (Editable) -->
            <div class="bg-[#E6F0FF] overflow-hidden shadow-sm sm:rounded-lg p-6 border border-[#CCE0FF]">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-[#004499] flex items-center">
                        <i class="fas fa-heartbeat mr-2"></i> Signos Vitales y Alergias
                    </h3>
                    @if($historialConsultas->count() > 0)
                    <button @click="showHistory = true" type="button" class="text-sm text-[#0061F5] hover:text-[#004499] font-medium flex items-center transition-colors duration-150">
                        <i class="fas fa-history mr-1"></i>
                        Ver Historial
                    </button>
                    @endif
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Peso</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="number" step="0.01" x-model="peso" class="focus:ring-[#0061F5] focus:border-[#0061F5] flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300" placeholder="0.00">
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                kg
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Estatura</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="number" step="0.01" x-model="estatura" class="focus:ring-[#0061F5] focus:border-[#0061F5] flex-1 block w-full rounded-none rounded-l-md sm:text-sm border-gray-300" placeholder="0.00">
                            <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                m
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alergias</label>
                        <textarea x-model="alergias" rows="1" class="shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Ninguna conocida..."></textarea>
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasRole('doctor'))
                @php
                    $servicioItems = $consultaCobro?->items?->where('tipo', 'servicio')->keyBy('servicio_id') ?? collect();
                @endphp
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-blue-100">
                    <div class="p-6">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between mb-5">
                            <div>
                                <h3 class="text-lg font-bold text-[#1E293B] flex items-center gap-2">
                                    <i class="fas fa-cash-register text-[#0061F5]"></i>
                                    {{ __('cobros.title') }}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">{{ __('cobros.help.doctor_instructions') }}</p>
                            </div>
                            <a href="{{ route('consulta-cobros.show', $cita) }}" class="inline-flex items-center px-3 py-2 bg-white text-[#0061F5] text-xs font-bold rounded-md border border-[#0061F5] hover:bg-blue-50 transition-colors">
                                <i class="fas fa-list-ul mr-2"></i>{{ __('cobros.actions.view_breakdown') }}
                            </a>
                        </div>

                        <form id="cobro-doctor-form" method="POST" action="{{ route('consulta-cobros.doctor.save', $cita) }}" @submit.prevent="previewCobro($event)">
                            @csrf
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="lg:col-span-2">
                                    <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('cobros.columns.select') }}</th>
                                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('servicios.columns.name') }}</th>
                                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('servicios.columns.duration') }}</th>
                                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('cobros.fields.catalog_price') }}</th>
                                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">{{ __('cobros.fields.charged_price') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @forelse($servicios as $servicio)
                                                    @php
                                                        $item = $servicioItems->get($servicio->id);
                                                    @endphp
                                                    <tr>
                                                        <td class="px-4 py-3">
                                                            <input type="checkbox" name="servicios[{{ $servicio->id }}][selected]" value="1" class="rounded border-gray-300 text-[#0061F5] focus:ring-[#0061F5]" {{ $item ? 'checked' : '' }}>
                                                        </td>
                                                        <td class="px-4 py-3 text-sm font-medium text-gray-700">{{ $servicio->nombre }}</td>
                                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $servicio->duracion }} min</td>
                                                        <td class="px-4 py-3 text-sm text-gray-500">${{ number_format($servicio->costo, 2) }}</td>
                                                        <td class="px-4 py-3">
                                                            <input type="number" step="0.01" min="0" name="servicios[{{ $servicio->id }}][precio_cobrado]" value="{{ old("servicios.{$servicio->id}.precio_cobrado", $item?->precio_cobrado ?? $servicio->costo) }}" class="w-32 rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] text-sm">
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('cobros.messages.no_services') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">{{ __('cobros.fields.instruction_status') }}</label>
                                        <div class="space-y-2">
                                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                                <input type="radio" name="estado_instrucciones" value="con_instrucciones" class="text-[#0061F5] focus:ring-[#0061F5]" {{ old('estado_instrucciones', $consultaCobro?->estado_instrucciones ?? 'con_instrucciones') === 'con_instrucciones' ? 'checked' : '' }}>
                                                {{ __('cobros.statuses.with_instructions') }}
                                            </label>
                                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                                <input type="radio" name="estado_instrucciones" value="sin_instrucciones" class="text-[#0061F5] focus:ring-[#0061F5]" {{ old('estado_instrucciones', $consultaCobro?->estado_instrucciones) === 'sin_instrucciones' ? 'checked' : '' }}>
                                                {{ __('cobros.statuses.without_instructions') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <label for="instrucciones_cobro" class="block text-sm font-bold text-gray-700">{{ __('cobros.fields.instructions') }}</label>
                                        <textarea id="instrucciones_cobro" name="instrucciones_cobro" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] text-sm" placeholder="{{ __('cobros.placeholders.instructions') }}">{{ old('instrucciones_cobro', $consultaCobro?->instrucciones_cobro) }}</textarea>
                                    </div>

                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-[#0061F5] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0051CC] transition">
                                        <i class="fas fa-paper-plane mr-2"></i>{{ __('cobros.actions.send_instructions') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div x-show="cobroModalOpen" style="display: none;" class="fixed inset-0 z-50" role="dialog" aria-modal="true">
                    <div class="absolute inset-0 bg-gray-500 bg-opacity-60"></div>
                    <div class="fixed inset-0 z-10 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <div class="w-full max-w-2xl bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100">
                                    <h3 class="text-lg font-bold text-gray-900">{{ __('cobros.affectations.warning_title') }}</h3>
                                    <p class="text-sm text-gray-500 mt-1" x-text="cobroPreviewSummary"></p>
                                </div>
                                <div class="p-6">
                                    <div class="space-y-3 max-h-72 overflow-y-auto">
                                        <template x-for="item in cobroAffected" :key="item.id">
                                            <div class="border border-red-100 bg-red-50 rounded-lg p-3">
                                                <div class="font-bold text-red-800" x-text="item.paciente"></div>
                                                <div class="text-sm text-red-700">
                                                    <span x-text="item.hora_inicio"></span>
                                                    <span> · </span>
                                                    <span x-text="item.telefono || '{{ __('cobros.ui.no_phone') }}'"></span>
                                                    <span> · </span>
                                                    <span x-text="item.email || '{{ __('cobros.ui.no_email') }}'"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <div class="px-6 py-4 bg-gray-50 flex justify-end gap-2">
                                    <button type="button" @click="cobroModalOpen = false" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors">{{ __('common.buttons.cancel') }}</button>
                                    <button type="button" @click="confirmCobroSubmit()" class="px-4 py-2 bg-red-600 text-white font-bold rounded-md hover:bg-red-700 transition-colors">{{ __('cobros.actions.confirm_affectations') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- History Modal -->
            <div x-show="showHistory" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div x-show="showHistory" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="showHistory" 
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             @click.away="showHistory = false"
                             class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">Historial de Signos Vitales</h3>
                                    <div class="mt-4 overflow-x-auto max-h-[60vh]">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50 sticky top-0">
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Peso</th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Estatura</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($historialConsultas as $hConsulta)
                                                    @if($hConsulta->peso || $hConsulta->estatura)
                                                    <tr class="hover:bg-gray-50 transition-colors">
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $hConsulta->created_at->format('d/m/Y') }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $hConsulta->peso ? $hConsulta->peso . ' kg' : '-' }}</td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $hConsulta->estatura ? $hConsulta->estatura . ' m' : '-' }}</td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @if($historialConsultas->whereNotNull('peso')->isEmpty() && $historialConsultas->whereNotNull('estatura')->isEmpty())
                                            <p class="text-sm text-gray-500 text-center py-4">No hay registros previos de signos vitales.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="button" @click="showHistory = false" class="inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:ml-3 sm:w-auto">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex" aria-label="Tabs">
                        <button @click="activeTab = 'consulta'" 
                            :class="{'border-[#0061F5] text-[#0061F5]': activeTab === 'consulta', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'consulta'}"
                            class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            <i class="fas fa-notes-medical mr-2"></i> Consulta
                        </button>
                        <button @click="activeTab = 'estudios'"
                            :class="{'border-[#0061F5] text-[#0061F5]': activeTab === 'estudios', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'estudios'}"
                            class="w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            <i class="fas fa-microscope mr-2"></i> Estudios
                        </button>
                    </nav>
                </div>

                <!-- Tab Content: Consulta -->
                <div x-show="activeTab === 'consulta'" class="p-6">
                    <form action="{{ route('consultas.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="cita_id" value="{{ $cita->id }}">
                        <input type="hidden" name="peso" :value="peso">
                        <input type="hidden" name="estatura" :value="estatura">
                        <input type="hidden" name="alergias" :value="alergias">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Plantilla</label>
                            <select name="plantilla_id" x-model="selectedPlantillaId" @change="loadPlantillaCampos()" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                                <option value="">Seleccione una plantilla</option>
                                @foreach($plantillas as $plantilla)
                                    <option value="{{ $plantilla->id }}">{{ $plantilla->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Dynamic Fields Area -->
                        <div class="space-y-4 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200 min-h-[200px]" id="campos-container">
                            <template x-if="!selectedPlantillaId">
                                <div class="text-center text-gray-500 py-10">
                                    <i class="fas fa-file-medical text-4xl mb-2 opacity-50"></i>
                                    <p>Seleccione una plantilla para comenzar a llenar la consulta.</p>
                                </div>
                            </template>
                            
                            <template x-for="campo in campos" :key="campo.id">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700" x-text="campo.etiqueta"></label>
                                    
                                    <!-- Text Input -->
                                    <template x-if="campo.tipo === 'text'">
                                        <input type="text" :name="'valores[' + campo.id + ']'" class="mt-1 focus:ring-[#0061F5] focus:border-[#0061F5] block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </template>
                                    
                                    <!-- Textarea -->
                                    <template x-if="campo.tipo === 'textarea'">
                                        <textarea :name="'valores[' + campo.id + ']'" rows="3" class="mt-1 shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    </template>
                                    
                                    <!-- Number -->
                                    <template x-if="campo.tipo === 'number'">
                                        <input type="number" :name="'valores[' + campo.id + ']'" class="mt-1 focus:ring-[#0061F5] focus:border-[#0061F5] block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </template>
                                    
                                    <!-- Select -->
                                    <template x-if="campo.tipo === 'select'">
                                        <select :name="'valores[' + campo.id + ']'" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#0061F5] focus:border-[#0061F5] sm:text-sm">
                                            <template x-for="opcion in campo.opciones.split(',')" :key="opcion">
                                                <option :value="opcion.trim()" x-text="opcion.trim()"></option>
                                            </template>
                                        </select>
                                    </template>
                                </div>
                            </template>
                        </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('citas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                            {{ __('common.buttons.cancel') }}
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0051CC] active:bg-[#004499] focus:outline-none focus:border-[#004499] focus:ring ring-[#80B0FA] disabled:opacity-25 transition ease-in-out duration-150">
                            <i class="fas fa-save mr-2"></i> Guardar Consulta
                        </button>
                        </div>
                    </form>

                    <!-- History List -->
                    <div class="mt-10">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Historial de Consultas</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Doctor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Plantilla</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($historialConsultas as $historia)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $historia->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $historia->doctor->name }} {{ $historia->doctor->apellido_paterno }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $historia->plantilla->nombre }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end items-center space-x-2">
                                                    @if(auth()->user()->hasRole('doctor'))
                                                        <a href="{{ route('consultas.edit', $historia) }}" class="inline-flex items-center justify-center w-10 h-10 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="Editar Consulta">
                                                            <i class="fas fa-edit text-xl"></i>
                                                        </a>
                                                    @elseif(auth()->user()->hasRole(['asistente','secretaria']))
                                                        <a href="{{ route('consultas.show', $historia) }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors shadow-sm" title="Ver Consulta">
                                                            <i class="fas fa-eye text-xl"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('consultas.edit', $historia) }}" class="inline-flex items-center justify-center w-10 h-10 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors shadow-sm" title="Editar">
                                                            <i class="fas fa-edit text-xl"></i>
                                                        </a>
                                                    @endif
                                                <a href="{{ route('consultas.print', $historia) }}" target="_blank" class="inline-flex items-center justify-center w-10 h-10 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition-colors shadow-sm" style="background-color: #1f2937;" title="Imprimir">
                                                    <i class="fas fa-print text-xl"></i>
                                                </a>
                                                @if(!auth()->user()->hasRole(['asistente','secretaria']))
                                                    <form action="{{ route('consultas.destroy', $historia) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar esta consulta?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex cursor-pointer items-center justify-center w-10 h-10 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="Borrar">
                                                            <i class="fas fa-trash text-xl"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay consultas previas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Estudios -->
                <div x-show="activeTab === 'estudios'" class="p-6" style="display: none;">
                    
                    @if($historialConsultas->count() > 0)
                        <!-- Select which consulta to attach study to, usually the latest one -->
                        <form action="{{ route('consultas.estudios.store', $historialConsultas->first()->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Orden de Estudio</label>
                                <textarea name="orden" rows="4" class="mt-1 shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Escriba los estudios requeridos..."></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                                <textarea name="observacion" rows="2" class="mt-1 shadow-sm focus:ring-[#0061F5] focus:border-[#0061F5] block w-full sm:text-sm border-gray-300 rounded-md" placeholder="Observaciones adicionales..."></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700">Adjuntar Imágenes/Archivos (Opcional)</label>
                                
                                <div 
                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md transition-colors duration-200"
                                    :class="{ 'border-[#0061F5] bg-[#E6F0FF]': isDragging }"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop($event)"
                                >
                                    <div class="space-y-1 text-center">
                                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2" :class="{ 'text-[#0061F5]': isDragging }"></i>
                                        <div class="flex text-sm text-gray-600 justify-center">
                                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-[#0061F5] hover:text-[#0061F5] focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-[#0061F5]">
                                                <span>Subir</span>
                                                <input id="file-upload" name="archivos[]" type="file" class="sr-only" multiple accept="image/*,.pdf" x-ref="fileInput" @change="handleFiles($event)">
                                            </label>
                                            <p class="pl-1">o arrastrar y soltar</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, PDF hasta 5MB</p>
                                    </div>
                                </div>

                                <!-- File List -->
                                <div class="mt-4 space-y-2" x-show="filesArray.length > 0">
                                    <template x-for="(file, index) in filesArray" :key="index">
                                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md border border-gray-200">
                                            <div class="flex items-center space-x-2 truncate">
                                                <i class="fas fa-file text-gray-400"></i>
                                                <span class="text-sm text-gray-600 truncate" x-text="file.name"></span>
                                                <span class="text-xs text-gray-400" x-text="(file.size / 1024).toFixed(2) + ' KB'"></span>
                                            </div>
                                            <button type="button" @click="removeFile(index)" class="text-red-500 hover:text-red-700">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="flex justify-end gap-2">
                                <a href="{{ route('citas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                                    {{ __('common.buttons.cancel') }}
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#0051CC] active:bg-[#004499] focus:outline-none focus:border-[#004499] focus:ring ring-[#80B0FA] disabled:opacity-25 transition ease-in-out duration-150">
                                    <i class="fas fa-save mr-2"></i> Guardar Orden
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="text-center p-10 bg-yellow-50 rounded-lg border border-yellow-100">
                            <p class="text-yellow-700">Primero debe guardar una consulta para poder crear una orden de estudios.</p>
                        </div>
                    @endif

                    <!-- Estudios History -->
                    <div class="mt-10">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">Historial de Estudios</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Fecha</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Doctor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Estudios</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($historialEstudios as $estudio)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $estudio->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $estudio->consulta->doctor->name }} {{ $estudio->consulta->doctor->apellido_paterno }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($estudio->orden, 50) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end items-center space-x-2">
                                                    <!-- Edit Button -->
                                                    <a href="{{ route('consultas.estudios.edit', $estudio) }}" class="inline-flex items-center justify-center w-10 h-10 bg-yellow-500 text-white rounded-md hover:bg-yellow-600 transition-colors shadow-sm" title="Editar">
                                                        <i class="fas fa-edit text-xl"></i>
                                                    </a>

                                                    <!-- Print Button -->
                                                    <a href="{{ route('consultas.estudios.print', $estudio) }}" target="_blank" class="inline-flex items-center justify-center w-10 h-10 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition-colors shadow-sm" style="background-color: #1f2937;" title="Imprimir">
                                                        <i class="fas fa-print text-xl"></i>
                                                    </a>

                                                    <!-- View Files Button -->
                                                    @if($estudio->archivos && $estudio->archivos->count() > 0)
                                                        <button type="button" @click="openFilesModal({{ $estudio->id }})" class="inline-flex items-center justify-center w-10 h-10 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors shadow-sm" title="Ver archivos">
                                                            <i class="fas fa-images text-xl"></i>
                                                        </button>
                                                    @endif

                                                    <!-- Delete Button -->
                                                    <form action="{{ route('consultas.estudios.destroy', $estudio) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar esta orden de estudio?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex cursor-pointer items-center justify-center w-10 h-10 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="Borrar">
                                                            <i class="fas fa-trash text-xl"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No hay órdenes de estudios previas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Files Modal -->
                    <div x-show="filesModalOpen" style="display: none;" class="relative z-50" aria-labelledby="files-modal-title" role="dialog" aria-modal="true">
                        <div x-show="filesModalOpen" 
                             x-transition:enter="ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <div x-show="filesModalOpen" 
                                     x-transition:enter="ease-out duration-300"
                                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave="ease-in duration-200"
                                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                     @click.away="closeFilesModal()"
                                     class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                                    
                                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                        <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                            <h3 class="text-base font-semibold leading-6 text-gray-900" id="files-modal-title">Archivos del estudio</h3>
                                            <p class="mt-2 text-sm text-gray-500">Selecciona un archivo para verlo o descargarlo.</p>
                                            <div class="mt-4 space-y-2 max-h-[60vh] overflow-y-auto">
                                                <template x-if="filesModalItems.length === 0">
                                                    <p class="text-sm text-gray-500">Este estudio no tiene archivos adjuntos.</p>
                                                </template>
                                                <template x-for="(archivo, index) in filesModalItems" :key="index">
                                                    <a :href="archivo.url" target="_blank" class="flex items-center justify-between px-4 py-2 bg-gray-50 rounded-md border border-gray-200 hover:bg-gray-100">
                                                        <div class="flex items-center space-x-2">
                                                            <i class="fas fa-file text-gray-400"></i>
                                                            <span class="text-sm text-gray-700 truncate" x-text="archivo.nombre"></span>
                                                        </div>
                                                        <span class="text-xs text-[#0061F5] font-medium">Abrir</span>
                                                    </a>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                        <button type="button" @click="closeFilesModal()" class="inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:ml-3 sm:w-auto">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="application/json" id="estudiosArchivosData">
        {!! json_encode(
            $historialEstudios->mapWithKeys(function ($estudio) {
                return [
                    $estudio->id => $estudio->archivos->map(function ($archivo) {
                        return [
                            'nombre' => $archivo->nombre_original,
                            'url' => asset($archivo->path),
                        ];
                    }),
                ];
            }),
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        ) !!}
    </script>

    <script>
        const estudiosArchivosEl = document.getElementById('estudiosArchivosData');
        window.estudiosArchivos = estudiosArchivosEl ? JSON.parse(estudiosArchivosEl.textContent || '{}') : {};

        function consultaHandler() {
            return {
                activeTab: 'consulta',
                peso: "{{ $paciente->peso ?? '' }}",
                estatura: "{{ $paciente->estatura ?? '' }}",
                alergias: "{{ $paciente->alergias ?? '' }}",
                selectedPlantillaId: '',
                campos: [],
                showHistory: false,
                filesModalOpen: false,
                filesModalItems: [],
                empathyOpen: false,
                empathyLoading: false,
                empathyNotes: [],
                empathyNewContent: '',
                empathyEditingId: null,
                empathyEditContent: '',
                empathyError: '',
                empathySearch: '',
                empathyPage: 1,
                empathyHasMore: false,
                empathyIndexUrl: "{{ route('pacientes.empatia.index', ['paciente' => $paciente->id]) }}",
                empathyStoreUrl: "{{ route('pacientes.empatia.store', ['paciente' => $paciente->id]) }}",
                empathyNoteBaseUrl: "{{ url('admin/pacientes/empatia') }}",
                cobroPreviewUrl: "{{ route('consulta-cobros.preview', $cita) }}",
                cobroPendingForm: null,
                cobroModalOpen: false,
                cobroAffected: [],
                cobroPreviewSummary: '',
                
                // File Upload Logic
                isDragging: false,
                filesArray: [],

                handleDrop(e) {
                    this.isDragging = false;
                    const droppedFiles = e.dataTransfer.files;
                    this.addFiles(droppedFiles);
                },

                handleFiles(e) {
                    const files = e.target.files;
                    this.addFiles(files);
                },

                addFiles(files) {
                    for(let i=0; i<files.length; i++) {
                        if(files[i].size <= 5242880) { // 5MB
                            // Check for duplicates?
                            this.filesArray.push(files[i]);
                        } else {
                            alert('El archivo ' + files[i].name + ' excede el límite de 5MB');
                        }
                    }
                    this.updateFileInput();
                },

                removeFile(index) {
                    this.filesArray.splice(index, 1);
                    this.updateFileInput();
                },

                updateFileInput() {
                    const dt = new DataTransfer();
                    this.filesArray.forEach(file => dt.items.add(file));
                    this.$refs.fileInput.files = dt.files;
                },

                openFilesModal(estudioId) {
                    if (window.estudiosArchivos && window.estudiosArchivos[estudioId]) {
                        this.filesModalItems = window.estudiosArchivos[estudioId];
                    } else {
                        this.filesModalItems = [];
                    }
                    this.filesModalOpen = true;
                },

                closeFilesModal() {
                    this.filesModalOpen = false;
                    this.filesModalItems = [];
                },
                    
                async loadPlantillaCampos() {
                    if (!this.selectedPlantillaId) {
                        this.campos = [];
                        return;
                    }
                    
                    try {
                        const response = await fetch(`/admin/plantillas/${this.selectedPlantillaId}/campos`);
                        const data = await response.json();
                        this.campos = data;
                    } catch (error) {
                        console.error('Error loading fields:', error);
                        this.campos = [];
                    }
                },

                toggleEmpatia() {
                    if (this.empathyOpen) {
                        this.closeEmpatia();
                        return;
                    }
                    this.openEmpatia();
                },

                openEmpatia() {
                    this.empathyOpen = true;
                    this.fetchEmpathyNotes(true);
                },

                closeEmpatia() {
                    this.empathyOpen = false;
                    this.empathyError = '';
                    this.empathyEditingId = null;
                    this.empathyEditContent = '';
                },

                getCsrfToken() {
                    const el = document.querySelector('meta[name="csrf-token"]');
                    return el ? el.getAttribute('content') : '';
                },

                async previewCobro(event) {
                    const form = event.target;
                    this.cobroPendingForm = form;

                    try {
                        const response = await fetch(this.cobroPreviewUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken(),
                            },
                            body: new FormData(form),
                        });

                        if (!response.ok) {
                            form.submit();
                            return;
                        }

                        const data = await response.json();
                        this.cobroAffected = data.affected || [];

                        if (this.cobroAffected.length === 0) {
                            form.submit();
                            return;
                        }

                        this.cobroPreviewSummary = '{{ __('cobros.affectations.warning_summary') }}'
                            .replace(':time', data.hora_fin_proyectada)
                            .replace(':count', this.cobroAffected.length);
                        this.cobroModalOpen = true;
                    } catch (error) {
                        form.submit();
                    }
                },

                confirmCobroSubmit() {
                    if (this.cobroPendingForm) {
                        this.cobroPendingForm.submit();
                    }
                },

                async fetchEmpathyNotes(reset = false) {
                    if (reset) {
                        this.empathyPage = 1;
                        this.empathyNotes = [];
                    }
                    this.empathyLoading = true;
                    this.empathyError = '';
                    try {
                        const url = new URL(this.empathyIndexUrl);
                        url.searchParams.append('page', this.empathyPage);
                        if (this.empathySearch) {
                            url.searchParams.append('search', this.empathySearch);
                        }

                        const response = await fetch(url.toString(), {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });
                        if (!response.ok) {
                            throw new Error('No se pudo cargar las notas.');
                        }
                        const data = await response.json();
                        
                        if (reset) {
                            this.empathyNotes = data.data || [];
                        } else {
                            this.empathyNotes = [...this.empathyNotes, ...(data.data || [])];
                        }
                        this.empathyHasMore = data.has_more || false;
                    } catch (error) {
                        if (reset) {
                            this.empathyNotes = [];
                        }
                        this.empathyError = error && error.message ? error.message : 'No se pudo cargar las notas.';
                    } finally {
                        this.empathyLoading = false;
                    }
                },

                async loadMoreEmpathyNotes() {
                    if (!this.empathyLoading && this.empathyHasMore) {
                        this.empathyPage++;
                        await this.fetchEmpathyNotes(false);
                    }
                },

                async saveEmpathyNote() {
                    const content = (this.empathyNewContent || '').trim();
                    if (!content) {
                        this.empathyError = 'Escribe una nota.';
                        return;
                    }

                    this.empathyError = '';
                    try {
                        const response = await fetch(this.empathyStoreUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken(),
                            },
                            body: JSON.stringify({ content }),
                        });

                        if (!response.ok) {
                            let message = 'No se pudo guardar la nota.';
                            try {
                                const data = await response.json();
                                if (data && data.message) {
                                    message = data.message;
                                }
                                if (data && data.errors && data.errors.content && data.errors.content[0]) {
                                    message = data.errors.content[0];
                                }
                            } catch (e) {
                            }
                            throw new Error(message);
                        }

                        this.empathyNewContent = '';
                        await this.fetchEmpathyNotes(true);
                    } catch (error) {
                        this.empathyError = error && error.message ? error.message : 'No se pudo guardar la nota.';
                    }
                },

                startEdit(note) {
                    this.empathyError = '';
                    this.empathyEditingId = note.id;
                    this.empathyEditContent = note.content || '';
                },

                cancelEdit() {
                    this.empathyEditingId = null;
                    this.empathyEditContent = '';
                },

                async updateEmpathyNote(noteId) {
                    const content = (this.empathyEditContent || '').trim();
                    if (!content) {
                        this.empathyError = 'Escribe una nota.';
                        return;
                    }

                    this.empathyError = '';
                    try {
                        const response = await fetch(`${this.empathyNoteBaseUrl}/${noteId}`, {
                            method: 'PUT',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken(),
                            },
                            body: JSON.stringify({ content }),
                        });

                        if (!response.ok) {
                            let message = 'No se pudo actualizar la nota.';
                            try {
                                const data = await response.json();
                                if (data && data.message) {
                                    message = data.message;
                                }
                                if (data && data.errors && data.errors.content && data.errors.content[0]) {
                                    message = data.errors.content[0];
                                }
                            } catch (e) {
                            }
                            throw new Error(message);
                        }

                        this.cancelEdit();
                        await this.fetchEmpathyNotes(true);
                    } catch (error) {
                        this.empathyError = error && error.message ? error.message : 'No se pudo actualizar la nota.';
                    }
                },

                async deleteEmpathyNote(noteId) {
                    this.empathyError = '';
                    if (!confirm('¿Eliminar esta nota de empatía?')) {
                        return;
                    }

                    try {
                        const response = await fetch(`${this.empathyNoteBaseUrl}/${noteId}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken(),
                            },
                        });

                        if (!response.ok) {
                            let message = 'No se pudo eliminar la nota.';
                            try {
                                const data = await response.json();
                                if (data && data.message) {
                                    message = data.message;
                                }
                            } catch (e) {
                            }
                            throw new Error(message);
                        }

                        await this.fetchEmpathyNotes(true);
                    } catch (error) {
                        this.empathyError = error && error.message ? error.message : 'No se pudo eliminar la nota.';
                    }
                }
            }
        }
    </script>
</x-admin-layout>
