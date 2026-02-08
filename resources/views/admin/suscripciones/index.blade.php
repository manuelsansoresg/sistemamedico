<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            Inicio
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Suscripciones</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Listado de Suscripciones</h2>
                        <a href="{{ route('admin.suscripciones.create') }}" class="cursor-pointer inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-[#0061F5] hover:bg-[#0051CC] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5]">
                            <i class="fas fa-plus mr-2"></i>
                            Nueva Suscripción
                        </a>
                    </div>

                    <form method="GET" action="{{ route('admin.suscripciones.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div class="col-span-1 md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700">Buscar Cliente</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nombre o email..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="date_start" class="block text-sm font-medium text-gray-700">Fecha Inicio</label>
                            <input type="date" name="date_start" id="date_start" value="{{ request('date_start') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="date_end" class="block text-sm font-medium text-gray-700">Fecha Fin</label>
                            <input type="date" name="date_end" id="date_end" value="{{ request('date_end') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div class="md:col-start-4 flex justify-end">
                            <button type="submit" class="cursor-pointer inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white hover:bg-[#0051CC] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5] bg-[#0061F5]">
                                Filtrar
                            </button>
                            @if(request()->hasAny(['search', 'date_start', 'date_end']))
                                <a href="{{ route('admin.suscripciones.index') }}" class="cursor-pointer ml-2 inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5]">
                                    Limpiar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">
                                        Doctor/Usuario
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">
                                        Item / Paquete
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">
                                        Tipo
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">
                                        Método de Pago
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">
                                        Estatus Pago
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">
                                        Estatus Cédula
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-[#0061F5] uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($suscripciones as $suscripcion)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-[#E6F0FF] flex items-center justify-center text-[#0061F5] font-bold">
                                                        {{ strtoupper(substr($suscripcion->user->name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-cyan-500">
                                                        {{ $suscripcion->user->name }}
                                                    </div>
                                                    <div class="text-sm text-cyan-500">
                                                        {{ $suscripcion->user->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($suscripcion->tipo == 'paquete')
                                                <div class="text-sm text-gray-900 font-bold">{{ optional($suscripcion->paquete)->nombre ?? 'Paquete Eliminado' }}</div>
                                            @else
                                                <div class="text-sm text-gray-900 font-bold">{{ optional($suscripcion->catalogo)->nombre ?? 'Item Eliminado' }}</div>
                                                @if($suscripcion->cantidad > 1)
                                                    <div class="text-xs text-gray-500">Cantidad: {{ $suscripcion->cantidad }}</div>
                                                @endif
                                            @endif
                                            <div class="text-sm text-gray-500">${{ number_format($suscripcion->precio, 2) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $suscripcion->tipo == 'paquete' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ ucfirst($suscripcion->tipo) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-[#E6F0FF] text-[#004499]">
                                                {{ ucfirst($suscripcion->metodo_pago) }}
                                            </span>
                                            @if($suscripcion->comprobante_pago)
                                                <span class="ml-2 text-xs text-green-600 font-bold">(Comprobante)</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $color = match($suscripcion->estatus_pago) {
                                                    'pagado' => 'green',
                                                    'pendiente' => 'yellow',
                                                    'fallido' => 'red',
                                                    default => 'gray',
                                                };
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                                {{ ucfirst($suscripcion->estatus_pago) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $cedulaColor = match($suscripcion->user->estatus_cedula) {
                                                    'validada' => 'green',
                                                    'pendiente' => 'yellow',
                                                    'rechazada' => 'red',
                                                    default => 'gray',
                                                };
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $cedulaColor }}-100 text-{{ $cedulaColor }}-800">
                                                {{ ucfirst($suscripcion->user->estatus_cedula ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $suscripcion->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                @if($suscripcion->estatus_pago == 'pendiente')
                                                    <form action="{{ route('admin.suscripciones.update', $suscripcion) }}" method="POST" onsubmit="return confirm('¿Estás seguro de activar esta suscripción?');">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="estatus_pago" value="pagado">
                                                        <button type="submit" class="inline-flex items-center justify-center w-9 h-9 text-white rounded-md hover:bg-green-600 transition-colors shadow-sm bg-green-500" title="Activar / Confirmar Pago">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <a href="{{ route('admin.suscripciones.show', $suscripcion) }}" class="inline-flex items-center justify-center w-9 h-9 text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm bg-[#0061F5]" title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $suscripciones->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
