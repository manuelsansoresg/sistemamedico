<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li class="inline-flex items-center">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('pacientes.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">
                                Pacientes
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Perfiles Compartidos</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Gestión de Perfiles Compartidos</h2>
                            @if(!$isRoot && $canSharePacientes === false)
                                <p class="mt-1 text-sm text-red-600">
                                    Para compartir perfiles de pacientes necesita una suscripción de Paciente activa.
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('pacientes.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-md hover:bg-gray-200 transition-colors shadow-sm flex items-center justify-center whitespace-nowrap">
                                <i class="fas fa-users mr-2"></i>
                                Ver todos los pacientes
                            </a>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mb-6">
                        <form action="{{ route('pacientes.shared.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Buscar</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" name="search" value="{{ request('search') }}" class="block w-full p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-[#0061F5] focus:border-[#0061F5] placeholder-gray-400" placeholder="Nombre, correo o CURP">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Estado del perfil</label>
                                <select name="estado" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-[#0061F5] focus:border-[#0061F5]">
                                    <option value="compartidos" {{ $estado === 'compartidos' ? 'selected' : '' }}>Solo compartidos</option>
                                    <option value="no_compartidos" {{ $estado === 'no_compartidos' ? 'selected' : '' }}>Solo no compartidos</option>
                                    <option value="todos" {{ $estado === 'todos' ? 'selected' : '' }}>Todos</option>
                                </select>
                            </div>

                            @if($isRoot)
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Doctor</label>
                                    <select name="doctor_id" class="block w-full p-2 text-sm text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-[#0061F5] focus:border-[#0061F5]">
                                        <option value="">Todos</option>
                                        @foreach($doctors as $doctor)
                                            <option value="{{ $doctor->id }}" {{ request('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                                {{ $doctor->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="flex items-end justify-start md:justify-end gap-2">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white text-sm font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm whitespace-nowrap">
                                    <i class="fas fa-filter mr-2"></i> Filtrar
                                </button>
                                @if(request()->hasAny(['search', 'estado', 'doctor_id']))
                                    <a href="{{ route('pacientes.shared.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-bold rounded-md hover:bg-gray-200 transition-colors shadow-sm whitespace-nowrap">
                                        Limpiar
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">NOMBRE</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">CORREO</th>
                                    @if($isRoot)
                                        <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">DOCTOR</th>
                                    @endif
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">PERFIL COMPARTIDO</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-[#0061F5] uppercase tracking-wider">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($pacientes as $paciente)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-cyan-500">
                                            {{ $paciente->name }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                                            @if($paciente->curp)
                                                <div class="text-xs text-gray-400 font-normal mt-1">{{ $paciente->curp }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-cyan-500">
                                            {{ $paciente->email }}
                                        </td>
                                        @if($isRoot)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                @php
                                                    $doctorFromRelation = $paciente->doctors->first();
                                                    $doctorFromCreatedBy = $doctors->firstWhere('id', $paciente->created_by);
                                                    $doctorName = $doctorFromRelation?->name ?? $doctorFromCreatedBy?->name;
                                                @endphp
                                                {{ $doctorName ?: 'Sin asignar' }}
                                            </td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($paciente->perfil_compartido)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                                    SÍ
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                                    NO
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                @if($isRoot)
                                                    @php
                                                        $confirmMsg = $paciente->perfil_compartido
                                                            ? '¿Quitar el perfil compartido de este paciente?'
                                                            : '¿Compartir el perfil de este paciente?';
                                                    @endphp
                                                    <form action="{{ route('pacientes.toggle_share', $paciente) }}" method="POST" class="inline-flex items-center" onsubmit="return confirm('{{ $confirmMsg }}');">
                                                        @csrf
                                                        @if(!$paciente->perfil_compartido)
                                                            <input type="hidden" name="doctor_id" value="{{ request('doctor_id') }}">
                                                        @endif
                                                        <button type="submit" class="inline-flex items-center justify-center w-9 h-9 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="{{ $paciente->perfil_compartido ? 'Quitar perfil compartido' : 'Compartir perfil' }}">
                                                            <i class="fas {{ $paciente->perfil_compartido ? 'fa-unlink' : 'fa-share-alt' }}"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    @if(!$paciente->perfil_compartido && $canSharePacientes)
                                                        <form action="{{ route('pacientes.share', $paciente) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Compartir el perfil de este paciente? Una vez compartido, no se podrá deshacer esta acción.');">
                                                            @csrf
                                                            <button type="submit" class="inline-flex items-center justify-center w-9 h-9 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="Compartir perfil">
                                                                <i class="fas fa-share-alt"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button type="button" class="inline-flex items-center justify-center w-9 h-9 bg-gray-100 text-gray-400 rounded-md cursor-not-allowed" title="Perfil ya compartido">
                                                            <i class="fas fa-share-alt"></i>
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isRoot ? 5 : 4 }}" class="px-6 py-4 text-center text-gray-500">
                                            No se encontraron pacientes para los filtros seleccionados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $pacientes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
