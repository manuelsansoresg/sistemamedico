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
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <a href="{{ route('admin.suscripciones.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">Suscripciones</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Detalle #{{ $suscripcion->id }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">Detalles de Suscripción #{{ $suscripcion->id }}</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información del Usuario y Paquete -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Información General</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Usuario</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $suscripcion->user->name }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $suscripcion->user->email }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Cédula Profesional</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $suscripcion->user->cedula_profesional }}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Paquete / Servicio</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if($suscripcion->tipo == 'paquete')
                                        {{ optional($suscripcion->paquete)->nombre ?? 'Paquete no encontrado' }}
                                    @else
                                        {{ optional($suscripcion->catalogo)->nombre ?? 'Servicio no encontrado' }}
                                        @if($suscripcion->cantidad > 1)
                                            <span class="text-xs text-gray-500">(x{{ $suscripcion->cantidad }})</span>
                                        @endif
                                    @endif
                                </dd>
                            </div>
                             <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Precio</dt>
                                <dd class="mt-1 text-sm text-gray-900">${{ number_format($suscripcion->precio, 2) }}</dd>
                            </div>
                             <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Método de Pago</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($suscripcion->metodo_pago) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Gestión de Pago -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Estado del Pago</h3>
                        
                        <form action="{{ route('admin.suscripciones.update', $suscripcion) }}" method="POST" class="mb-6">
                            @csrf
                            @method('PUT')
                            <div class="flex items-end gap-4">
                                <div class="flex-1">
                                    <label for="estatus_pago" class="block text-sm font-medium text-gray-700">Estatus Actual</label>
                                    <select id="estatus_pago" name="estatus_pago" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="pendiente" {{ $suscripcion->estatus_pago == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="pagado" {{ $suscripcion->estatus_pago == 'pagado' ? 'selected' : '' }}>Pagado</option>
                                        <option value="fallido" {{ $suscripcion->estatus_pago == 'fallido' ? 'selected' : '' }}>Fallido</option>
                                        <option value="vencido" {{ $suscripcion->estatus_pago == 'vencido' ? 'selected' : '' }}>Vencido</option>
                                    </select>
                                </div>
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white hover:bg-[#0051CC] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5] bg-[#0061F5]">
                                    Actualizar Pago
                                </button>
                            </div>
                        </form>

                        <div class="mt-6 border-t pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Acceso al Sistema</h4>
                            <form action="{{ route('admin.suscripciones.update', $suscripcion) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-700">Permitir acceso al usuario (Activar Cuenta)</span>
                                    <div class="flex items-center gap-2">
                                        <!-- Hidden input for false value when unchecked -->
                                        <input type="hidden" name="user_activo" value="0">
                                        <label for="user_activo_toggle" class="flex items-center cursor-pointer relative">
                                            <input type="checkbox" id="user_activo_toggle" name="user_activo" value="1" class="sr-only" {{ $suscripcion->user->activo ? 'checked' : '' }} onchange="this.form.submit()">
                                            <div class="w-11 h-6 bg-gray-200 rounded-full border border-gray-200 toggle-bg"></div>
                                            <span class="ml-3 text-sm font-medium text-gray-900">{{ $suscripcion->user->activo ? 'Activo' : 'Inactivo' }}</span>
                                        </label>
                                    </div>
                                </div>
                                <style>
                                    #user_activo_toggle:checked + .toggle-bg {
                                        background-color: #0061F5;
                                        border-color: #0061F5;
                                    }
                                    #user_activo_toggle:checked + .toggle-bg:after {
                                        transform: translateX(100%);
                                        border-color: white;
                                    }
                                    .toggle-bg:after {
                                        content: '';
                                        position: absolute;
                                        top: 2px;
                                        left: 2px;
                                        width: 20px;
                                        height: 20px;
                                        background-color: white;
                                        border-radius: 50%;
                                        transition: all 0.3s;
                                        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
                                    }
                                </style>
                            </form>
                        </div>

                        @if($suscripcion->comprobante_pago)
                            <div class="mt-6 border-t pt-4">
                                <h4 class="text-sm font-medium text-gray-900 mb-2">Comprobante de Pago</h4>
                                <div class="flex items-center gap-4">
                                    <a href="{{ route('admin.suscripciones.download_comprobante', $suscripcion) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5]">
                                        <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Descargar Comprobante
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="mt-6 border-t pt-4">
                                <p class="text-sm text-gray-500 italic">No se ha subido comprobante de pago.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Validación de Cédula (Solo si es doctor) -->
                @if($suscripcion->user->hasRole('doctor'))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg md:col-span-2">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Validación de Cédula Profesional</h3>
                        
                        <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Estatus Actual</p>
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
                            </div>

                            <form action="{{ route('admin.users.validar_cedula', $suscripcion->user) }}" method="POST" class="flex gap-4">
                                @csrf
                                <button type="submit" name="accion" value="validar" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Validar Cédula
                                </button>
                                <button type="submit" name="accion" value="rechazar" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Rechazar
                                </button>
                            </form>
                        </div>

                        @if($suscripcion->user->cedula_profesional)
                            <div class="mt-4">
                                <p class="text-sm text-gray-500 mb-2">Cédula proporcionada:</p>
                                <div class="bg-gray-100 p-3 rounded text-mono font-medium">
                                    {{ $suscripcion->user->cedula_profesional }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>