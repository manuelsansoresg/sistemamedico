<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Gestión de Horarios</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ __('Gestión de Horarios por Médico') }}
                    </h2>
                    <p class="text-gray-600 text-sm mt-1">Selecciona un médico y consultorio para gestionar su disponibilidad.</p>
                </div>
                
                <form action="{{ route('horarios.index') }}" method="GET" class="flex items-center">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" class="block w-64 p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400" placeholder="Buscar médico...">
                    </div>
                    <button type="submit" class="ml-2 text-white bg-blue-600 hover:bg-blue-700 font-medium rounded-md text-sm px-4 py-2 shadow-sm">Buscar</button>
                    @if(request('search'))
                        <a href="{{ route('horarios.index') }}" class="ml-2 text-gray-500 hover:text-gray-700 text-sm">Limpiar</a>
                    @endif
                </form>
            </div>

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">¡Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($users as $user)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-xl mr-4">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg text-gray-900">{{ $user->name }} {{ $user->apellido_paterno }}</h3>
                                    <p class="text-sm text-gray-500">{{ $user->especialidad->nombre ?? 'Sin especialidad' }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Consultorios Asignados</h4>
                                @forelse($user->consultorios as $consultorio)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm font-medium text-gray-700">{{ $consultorio->nombre }}</span>
                                        <form action="{{ route('horarios.manage') }}" method="GET">
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <input type="hidden" name="consultorio_id" value="{{ $consultorio->id }}">
                                            <button type="submit" class="px-3 py-1 bg-white border border-blue-600 text-blue-600 text-xs font-bold rounded hover:bg-blue-600 hover:text-white transition-colors">
                                                GESTIONAR
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400 italic">No tiene consultorios asignados.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-8 text-center rounded-lg shadow-sm">
                        <p class="text-gray-500 text-lg">No hay usuarios con consultorios asignados.</p>
                        <a href="{{ route('users.index') }}" class="text-blue-600 hover:underline mt-2 inline-block">Ir a gestión de usuarios</a>
                    </div>
                @endforelse
            </div>
            
            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
