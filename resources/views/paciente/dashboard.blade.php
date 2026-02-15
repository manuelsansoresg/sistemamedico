<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 px-2">
                <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide text-center">MI PERFIL</span>
                </a>
                <a href="{{ route('paciente.expedientes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-folder-open text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide text-center">MIS EXPEDIENTES</span>
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="inline-block w-6 h-6 text-[#0061F5] mr-2"><i class="fas fa-user"></i></span> Mi Perfil
                        </h3>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm gap-2">
                            <span>Editar Perfil</span><i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <p class="text-gray-600 text-sm">Actualiza tu información personal y de contacto.</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <span class="inline-block w-6 h-6 text-[#0061F5] mr-2"><i class="fas fa-folder-open"></i></span> Mis Expedientes
                        </h3>
                        <a href="{{ route('paciente.expedientes.index') }}" class="inline-flex items-center gap-2 text-sm text-[#0061F5] hover:text-[#0051CC]">
                            <span>Ver todos</span><i class="fas fa-chevron-right"></i>
                        </a>
                    </div>

                    @if(isset($consultas) && $consultas->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Doctor</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Clínica</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Consultorio</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($consultas as $consulta)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                                {{ optional($consulta->cita?->fecha)->format('d/m/Y') ?? $consulta->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">
                                                {{ $consulta->doctor?->name }} {{ $consulta->doctor?->apellido_paterno }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">
                                                {{ $consulta->cita?->clinica?->nombre ?? '—' }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">
                                                {{ $consulta->cita?->consultorio?->nombre ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 italic">Aún no tienes expedientes registrados.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
