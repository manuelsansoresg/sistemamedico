<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Panel Médico') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Section 1: Citas del Día -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <i class="fas fa-calendar-day text-blue-500 mr-2"></i> Citas de Hoy
                        </h3>
                        <a href="{{ route('citas.index') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                            Ver más <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    @if($citasHoy->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Hora</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Paciente</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Consultorio</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($citasHoy as $cita)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $cita->hora_inicio->format('g:i A') }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">
                                                {{ $cita->paciente->name }} {{ $cita->paciente->apellido_paterno }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">
                                                {{ $cita->consultorio->nombre }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($cita->estado === 'pendiente') bg-yellow-100 text-yellow-800 
                                                    @elseif($cita->estado === 'confirmada') bg-green-100 text-green-800 
                                                    @elseif($cita->estado === 'cancelada') bg-red-100 text-red-800 
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($cita->estado) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 italic">No hay citas programadas para hoy.</p>
                    @endif
                </div>
            </div>

            <!-- Section 2: Pendientes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <i class="fas fa-tasks text-orange-500 mr-2"></i> Pendientes / Recordatorios
                        </h3>
                        <a href="{{ route('pendientes.index') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                            Ver más <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    @if($pendientes->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($pendientes as $pendiente)
                                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100 shadow-sm relative hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs font-bold text-gray-500 bg-white px-2 py-1 rounded border">
                                            {{ $pendiente->fecha->format('d/m/Y') }} - {{ $pendiente->hora->format('g:i A') }}
                                        </span>
                                        <a href="{{ route('pendientes.edit', $pendiente) }}" class="text-gray-400 hover:text-blue-500">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    </div>
                                    <p class="text-gray-800 text-sm font-medium line-clamp-3">
                                        {{ $pendiente->recordatorio }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 italic">No hay pendientes activos.</p>
                        <a href="{{ route('pendientes.create') }}" class="mt-2 inline-flex items-center text-sm text-blue-600 hover:underline">
                            <i class="fas fa-plus mr-1"></i> Crear nuevo recordatorio
                        </a>
                    @endif
                </div>
            </div>

            <!-- Section 3: Accesos Rápidos (Iconos) -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 px-4">
                
                <!-- Usuarios -->
                <a href="{{ route('users.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">USUARIOS</span>
                </a>

                <!-- Clínica -->
                <a href="{{ route('clinicas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-hospital-alt text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">CLÍNICA</span>
                </a>

                <!-- Consultorios -->
                <a href="{{ route('consultorios.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-building text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">CONSULTORIOS</span>
                </a>

                <!-- Horarios -->
                <a href="{{ route('horarios.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-clock text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">HORARIOS</span>
                </a>

                <!-- Pacientes -->
                <a href="{{ route('pacientes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">PACIENTES</span>
                </a>

                <!-- Citas -->
                <a href="{{ route('citas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-calendar-alt text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">CITAS</span>
                </a>

                <!-- Pendientes -->
                <a href="{{ route('pendientes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-clipboard-list text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">PENDIENTES</span>
                </a>

                <!-- Plantillas -->
                <a href="{{ route('plantillas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-file-medical-alt text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">PLANTILLAS</span>
                </a>

            </div>

        </div>
    </div>
</x-app-layout>
