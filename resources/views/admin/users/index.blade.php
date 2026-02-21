<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Usuarios</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Listado de Usuarios</h2>
                        <a href="{{ route('users.create') }}" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm flex items-center">
                            <i class="fas fa-plus mr-2"></i> NUEVO USUARIO
                        </a>
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

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">NOMBRE / EMAIL</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">ROL</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">CLÍNICAS</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">CONSULTORIOS</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-[#0061F5] uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-[#E6F0FF] flex items-center justify-center text-[#0061F5] font-bold">
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-cyan-500">{{ $user->name }}</div>
                                                    <div class="text-sm text-cyan-500">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @foreach($user->roles as $role)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-[#E6F0FF] text-[#0051CC]">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($user->clinicas as $clinica)
                                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-md">
                                                        {{ $clinica->nombre }}
                                                    </span>
                                                @empty
                                                    <span class="text-gray-400 text-sm">-</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                @forelse($user->consultorios as $consultorio)
                                                    <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-md">
                                                        {{ $consultorio->nombre }}
                                                    </span>
                                                @empty
                                                    <span class="text-gray-400 text-sm">-</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <a href="{{ route('users.edit', $user) }}" class="inline-flex items-center justify-center w-9 h-9 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex cursor-pointer items-center justify-center w-9 h-9 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
