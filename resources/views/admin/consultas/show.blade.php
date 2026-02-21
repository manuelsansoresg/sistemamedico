<x-admin-layout>
    <div class="py-12">
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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Consulta</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-[#0061F5]">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ $consulta->paciente->name }} {{ $consulta->paciente->apellido_paterno }} {{ $consulta->paciente->apellido_materno }}
                        </h2>
                        <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-600">
                            <span class="flex items-center"><i class="fas fa-user-md mr-2 text-[#0061F5]"></i> Doctor: {{ $consulta->doctor->name }} {{ $consulta->doctor->apellido_paterno }}</span>
                            <span class="flex items-center"><i class="fas fa-clinic-medical mr-2 text-[#0061F5]"></i> Consultorio: {{ $consulta->cita?->consultorio?->nombre ?? '—' }}</span>
                            <span class="flex items-center"><i class="fas fa-calendar-alt mr-2 text-[#0061F5]"></i> {{ optional($consulta->created_at)->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    <div class="text-right space-x-2">
                        <a href="{{ route('consultas.print', $consulta) }}" target="_blank" class="inline-flex items-center justify-center w-10 h-10 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition-colors shadow-sm" title="Imprimir" style="background-color:#1f2937;">
                            <i class="fas fa-print text-xl"></i>
                        </a>
                        @if(auth()->user()->hasRole('doctor') && $consulta->doctor_id === auth()->id())
                            <a href="{{ route('consultas.edit', $consulta) }}" class="inline-flex items-center justify-center w-10 h-10 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="Editar">
                                <i class="fas fa-edit text-xl"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Datos -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Peso</label>
                        <div class="px-3 py-2 border border-gray-200 rounded-md bg-gray-50">{{ $consulta->peso ?? '—' }} kg</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Estatura</label>
                        <div class="px-3 py-2 border border-gray-200 rounded-md bg-gray-50">{{ $consulta->estatura ?? '—' }} m</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Alergias</label>
                        <div class="px-3 py-2 border border-gray-200 rounded-md bg-gray-50">{{ $consulta->alergias ?: '—' }}</div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Plantilla</label>
                    <div class="px-3 py-2 border border-gray-200 rounded-md bg-gray-50">{{ $consulta->plantilla?->nombre ?? '—' }}</div>
                </div>

                @if($consulta->valores && $consulta->valores->count() > 0)
                    <div class="space-y-4">
                        @foreach($consulta->valores as $valor)
                            <div>
                                <label class="block text-sm font-medium text-gray-500">{{ $valor->campo?->etiqueta ?? 'Campo' }}</label>
                                <div class="mt-1 px-3 py-2 border border-gray-200 rounded-md bg-gray-50">{{ $valor->valor }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">No hay valores capturados para esta consulta.</p>
                @endif

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-microscope text-[#0061F5] mr-2"></i> Estudios
                    </h3>

                    @if($consulta->estudios && $consulta->estudios->count() > 0)
                        <div class="space-y-4">
                            @foreach($consulta->estudios as $estudio)
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">Orden</p>
                                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $estudio->orden }}</p>
                                        </div>
                                        @if(auth()->user()->hasRole('root') || (auth()->user()->hasRole('doctor') && $consulta->doctor_id === auth()->id()))
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('consultas.estudios.edit', $estudio) }}" class="inline-flex items-center justify-center w-9 h-9 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="Editar estudio">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </a>
                                                <a href="{{ route('consultas.estudios.print', $estudio) }}" target="_blank" class="inline-flex items-center justify-center w-9 h-9 bg-gray-800 text-white rounded-md hover:bg-gray-900 transition-colors shadow-sm" title="Imprimir estudio" style="background-color: #1f2937;">
                                                    <i class="fas fa-print text-sm"></i>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                    @if($estudio->observacion)
                                        <div class="mt-2">
                                            <p class="text-xs font-semibold text-gray-600">Observación</p>
                                            <p class="text-xs text-gray-700 whitespace-pre-line">{{ $estudio->observacion }}</p>
                                        </div>
                                    @endif
                                    @if($estudio->archivos && $estudio->archivos->count() > 0)
                                        <div class="mt-3">
                                            <p class="text-xs font-semibold text-gray-600 mb-1">Archivos</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($estudio->archivos as $archivo)
                                                    <a href="{{ asset($archivo->path) }}" target="_blank" class="inline-flex items-center px-2 py-1 text-xs bg-white border border-gray-300 rounded-md text-[#0061F5] hover:bg-gray-50">
                                                        <i class="fas fa-paperclip mr-1"></i> {{ $archivo->nombre_original }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No hay estudios registrados para esta consulta.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
