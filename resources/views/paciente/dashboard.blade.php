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
                    </div>

                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Clínica</label>
                            <select name="clinica_id" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]">
                                <option value="">Todas</option>
                                @foreach($clinicas as $c)
                                    <option value="{{ $c->id }}" @selected(request('clinica_id') == $c->id)>{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Consultorio</label>
                            <select name="consultorio_id" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]">
                                <option value="">Todos</option>
                                @foreach($consultorios as $co)
                                    <option value="{{ $co->id }}" @selected(request('consultorio_id') == $co->id)>{{ $co->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Desde</label>
                            <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]" />
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Hasta</label>
                            <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="w-full border-gray-300 rounded-md focus:ring-[#0061F5] focus:border-[#0061F5]" />
                        </div>
                        <div class="md:col-span-4">
                            <button class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                <i class="fas fa-filter mr-2"></i> Filtrar
                            </button>
                            @if(request()->hasAny(['clinica_id','consultorio_id','fecha_inicio','fecha_fin']))
                                <a href="{{ route('dashboard') }}" class="ml-3 text-sm text-gray-600 hover:text-gray-800">Limpiar</a>
                            @endif
                        </div>
                    </form>

                    @if($expedientes->count() > 0)
                        <form method="POST" action="{{ route('paciente.expedientes.download.bulk') }}">
                            @csrf
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-sm text-gray-600">
                                    Selecciona una o varias consultas para descargar tu expediente en ZIP.
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white text-sm font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                        <i class="fas fa-download mr-2"></i> Descargar seleccionados
                                    </button>
                                    <a href="{{ route('paciente.expedientes.download.all', request()->query()) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-semibold text-gray-700 rounded-md hover:bg-gray-50 transition-colors shadow-sm">
                                        <i class="fas fa-file-archive mr-2"></i> Descargar todo
                                    </a>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 w-10">
                                                <input type="checkbox" onclick="document.querySelectorAll('.expediente-checkbox').forEach(cb => cb.checked = this.checked)" />
                                            </th>
                                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha</th>
                                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Doctor</th>
                                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Clínica</th>
                                            <th class="px-4 py-2 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Consultorio</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($expedientes as $consulta)
                                            <tr>
                                                <td class="px-4 py-2">
                                                    <input type="checkbox" name="selected[]" value="{{ $consulta->id }}" class="expediente-checkbox" />
                                                </td>
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

                            <div class="mt-4">
                                {{ $expedientes->links() }}
                            </div>
                        </form>
                    @else
                        <p class="text-gray-500 italic">Aún no tienes expedientes registrados.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
