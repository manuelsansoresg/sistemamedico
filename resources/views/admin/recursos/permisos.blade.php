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
                    <li class="inline-flex items-center">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('recursos.index', ['doctor_id' => $doctorId]) }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">
                                Recursos Compartidos
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Permisos</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">Permisos de Recursos</h2>
                            <p class="mt-1 text-sm text-gray-500">Define qué usuarios pueden gestionar la agenda de recursos del doctor.</p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            @if(auth()->user()->hasRole('root'))
                                <form method="GET" action="{{ route('recursos.permisos') }}" class="flex items-center gap-2">
                                    <label class="text-xs font-semibold text-gray-500">Doctor</label>
                                    <select name="doctor_id" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#0061F5] focus:border-[#0061F5]">
                                        @foreach($doctors as $doc)
                                            <option value="{{ $doc->id }}" @selected($doctorId === $doc->id)>{{ $doc->name }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                            <a href="{{ route('recursos.agenda', ['doctor_id' => $doctorId]) }}" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white text-sm font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                <i class="fas fa-calendar-alt mr-2"></i>
                                Ir a agenda
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('recursos.permisos.actualizar') }}" method="POST">
                        @csrf
                        <input type="hidden" name="doctor_id" value="{{ $doctorId }}">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">Usuario</th>
                                        <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">Rol</th>
                                        <th class="px-6 py-3 text-center text-xs font-bold text-[#0061F5] uppercase tracking-wider">Puede gestionar recursos</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($usuarios as $usuario)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $usuario->name }} {{ $usuario->apellido_paterno }}
                                                <div class="text-xs text-gray-500">{{ $usuario->email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $usuario->roles->pluck('name')->implode(', ') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                                @php
                                                    $isDoctorPrincipal = $usuario->id === $doctor->id;
                                                @endphp
                                                @if($isDoctorPrincipal)
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                                                        Siempre activo
                                                    </span>
                                                @else
                                                    <input
                                                        type="checkbox"
                                                        name="usuarios[]"
                                                        value="{{ $usuario->id }}"
                                                        class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]"
                                                        @checked($usuario->hasPermissionTo('manage recursos'))>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-2 bg-[#0061F5] text-white text-sm font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                <i class="fas fa-save mr-2"></i>
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
