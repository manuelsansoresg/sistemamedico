<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ auth()->user()->hasRole('root') ? route('admin.dashboard') : route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Recursos Compartidos</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Recursos Compartidos</h2>
                            <p class="mt-1 text-sm text-gray-500">Quirofanos, salas de juntas u otros recursos comunes.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('recursos.agenda', ['doctor_id' => $doctorId]) }}" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white text-sm font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Agenda de recursos
                            </a>
                            @if(auth()->user()->hasRole(['root', 'doctor']))
                                <a href="{{ route('recursos.permisos', ['doctor_id' => $doctorId]) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-bold rounded-md hover:bg-gray-200 transition-colors shadow-sm">
                                    <i class="fas fa-user-shield mr-2"></i>
                                    Permisos
                                </a>
                            @endif
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-1">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-bold text-gray-800">Nuevo recurso</h3>
                            </div>
                            <form action="{{ route('recursos.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="doctor_id" value="{{ $doctorId }}">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Nombre</label>
                                    <input type="text" name="nombre" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Tipo</label>
                                    <input type="text" name="tipo" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]" placeholder="Quirofano, sala de juntas, equipo">
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex-1">
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Color</label>
                                        <input type="text" name="color" value="#0061F5" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]">
                                    </div>
                                    <div class="mt-6">
                                        <input type="color" value="#0061F5" class="w-10 h-10 border border-gray-300 rounded-md cursor-pointer" oninput="this.previousElementSibling.querySelector('input[name=color]').value = this.value">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Descripción</label>
                                    <textarea name="descripcion" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]"></textarea>
                                </div>
                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white text-sm font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                        <i class="fas fa-plus mr-2"></i>
                                        Agregar recurso
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="lg:col-span-2">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                                <div class="flex items-center gap-2">
                                    @if(auth()->user()->hasRole('root'))
                                        <form method="GET" action="{{ route('recursos.index') }}" class="flex items-center gap-2">
                                            <label class="text-xs font-semibold text-gray-500">Doctor</label>
                                            <select name="doctor_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]">
                                                @foreach($doctors as $doctor)
                                                    <option value="{{ $doctor->id }}" @selected($doctorId === $doctor->id)>{{ $doctor->name }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-500">Recursos del doctor actual</span>
                                    @endif
                                </div>

                                <form method="GET" action="{{ route('recursos.index') }}" class="flex items-center gap-2">
                                    <input type="hidden" name="doctor_id" value="{{ $doctorId }}">
                                    <label class="text-xs font-semibold text-gray-500">Estado</label>
                                    <select name="activo" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]">
                                        <option value="">Todos</option>
                                        <option value="1" @selected(request('activo') === '1')>Solo activos</option>
                                        <option value="0" @selected(request('activo') === '0')>Solo inactivos</option>
                                    </select>
                                </form>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">Recurso</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">Tipo</th>
                                            <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">Estado</th>
                                            <th class="px-6 py-3 text-right text-xs font-bold text-[#0061F5] uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse($recursos as $recurso)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    <div class="flex items-center gap-2">
                                                        <span class="inline-block w-3 h-3 rounded-full" style="background-color: {{ $recurso->color }}"></span>
                                                        {{ $recurso->nombre }}
                                                    </div>
                                                    @if($recurso->descripcion)
                                                        <div class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $recurso->descripcion }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    {{ $recurso->tipo ?: 'Sin especificar' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    @if($recurso->activo)
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Activo</span>
                                                    @else
                                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">Inactivo</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex justify-end items-center gap-2">
                                                        <form action="{{ route('recursos.update', $recurso) }}" method="POST" class="inline-flex items-center gap-2">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="nombre" value="{{ $recurso->nombre }}">
                                                            <input type="hidden" name="tipo" value="{{ $recurso->tipo }}">
                                                            <input type="hidden" name="color" value="{{ $recurso->color }}">
                                                            <input type="hidden" name="descripcion" value="{{ $recurso->descripcion }}">
                                                            <input type="hidden" name="activo" value="{{ $recurso->activo ? 0 : 1 }}">
                                                            <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-md {{ $recurso->activo ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }} transition-colors shadow-sm" title="{{ $recurso->activo ? 'Desactivar' : 'Activar' }}">
                                                                <i class="fas {{ $recurso->activo ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('recursos.destroy', $recurso) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar este recurso?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex cursor-pointer items-center justify-center w-9 h-9 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="Eliminar">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                                    No hay recursos registrados para este doctor.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                {{ $recursos->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
