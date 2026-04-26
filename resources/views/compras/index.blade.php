<x-admin-layout>
    <script type="application/json" id="catalog-items-json">
        {!! json_encode($catalogos->map(function($item) {
            return [
                'id' => $item->id,
                'nombre' => $item->nombre,
                'precio' => $item->precio
            ];
        })->values(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>
    <div class="py-10" x-data="{ 
        purchaseModalOpen: false, 
        uploadModalOpen: false,
        renewModalOpen: false,
        selectedItem: null,
        selectedSubscriptionId: null,
        selectedRenewSubscriptionId: null,
        metodoPago: 'tarjeta',
        metodoPagoRenovar: 'tarjeta',
        cantidad: 1,
        openModal(id) {
            this.selectedItem = window.catalogItems.find(i => i.id == id);
            this.cantidad = 1;
            this.purchaseModalOpen = true;
        },
        openUploadModal(subscriptionId) {
            this.selectedSubscriptionId = subscriptionId;
            this.uploadModalOpen = true;
        },
        openRenewModal(subscriptionId) {
            this.selectedRenewSubscriptionId = subscriptionId;
            this.metodoPagoRenovar = 'tarjeta';
            this.renewModalOpen = true;
        }
    }">
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
                            <span class="ml-1 text-sm font-medium text-gray-700 md:ml-2">Suscripciones</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <h1 class="text-2xl font-bold text-gray-800 mb-6">Gestión de Suscripciones</h1>

            <!-- Mis Suscripciones -->
            <div class="mb-10">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i> Mis Suscripciones Activas
                </h2>
                
                @if($suscripciones->isEmpty())
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 text-center">
                        <p class="text-gray-500">No tienes suscripciones activas.</p>
                    </div>
                @else
                    <div class="bg-white shadow-sm overflow-hidden sm:rounded-lg border border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">SERVICIO / PAQUETE</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">TIPO</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">DETALLE</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">ESTADO PAGO</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">VIGENCIA</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">ACCIONES</th>
                                    </tr>
                                </thead>
                                @php
                                    $limitesGlobales = app(\App\Services\SubscriptionService::class)->calculateLimits(auth()->user());
                                    $currentCounts = [
                                        'clinicas' => \App\Models\Clinica::where('created_by', auth()->id())->count(),
                                        'consultorios' => \App\Models\Consultorio::where('created_by', auth()->id())->count(),
                                        'usuarios' => \App\Models\User::where('created_by', auth()->id())->whereHas('roles', function ($q) { $q->whereIn('name', ['asistente','secretaria']); })->count(),
                                        'pacientes' => \App\Models\User::role('paciente')->where('created_by', auth()->id())->count(),
                                    ];
                                @endphp
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($suscripciones as $sub)
                                        @php
                                            $paqueteVencido = $sub->tipo === 'paquete'
                                                && $sub->estatus_pago === 'pagado'
                                                && $sub->fecha_fin
                                                && \Carbon\Carbon::parse($sub->fecha_fin)->lt(\Carbon\Carbon::now());
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-700">
                                                    @if($sub->tipo == 'paquete')
                                                        {{ optional($sub->paquete)->nombre ?? 'Paquete' }}
                                                    @else
                                                        {{ optional($sub->catalogo)->nombre ?? 'Servicio' }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sub->tipo == 'paquete' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ ucfirst($sub->tipo) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($sub->tipo == 'individual')
                                                    @php
                                                        $isPaciente = optional($sub->catalogo) && str_contains(strtolower($sub->catalogo->nombre), 'paciente');
                                                        $asignados = $isPaciente ? $sub->pacientes()->select('users.id','users.name','users.apellido_paterno','users.apellido_materno')->get() : collect();
                                                        $usados = $asignados->count();
                                                        $capacidad = $sub->cantidad ?? 0;
                                                        $restantes = max(0, $capacidad - $usados);
                                                    @endphp
                                                    <div class="space-y-1">
                                                        <div>
                                                            Cantidad adquirida: {{ $sub->cantidad ?? 0 }}
                                                        </div>
                                                        @if($isPaciente)
                                                            <div class="text-xs text-gray-600">
                                                                Asignados: {{ $usados }} / {{ $capacidad }} @if($restantes>0)<span class="ml-1 text-green-600">(Restantes: {{ $restantes }})</span>@endif
                                                            </div>
                                                            @if($asignados->isNotEmpty())
                                                                <div class="mt-1 text-xs text-gray-700">
                                                                    @foreach($asignados as $p)
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 mr-1 mb-1">
                                                                            {{ $p->name }} {{ $p->apellido_paterno }} {{ $p->apellido_materno }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @elseif($sub->tipo == 'paquete' && $sub->paquete)
                                                    <div class="space-y-1">
                                                        @foreach($sub->paquete->catalogos as $cat)
                                                            @php
                                                                $nombre = strtolower($cat->nombre ?? '');
                                                                $key = null;
                                                                if (str_contains($nombre, 'clínica') || str_contains($nombre, 'clinica')) $key = 'clinicas';
                                                                elseif (str_contains($nombre, 'consultorio')) $key = 'consultorios';
                                                                elseif (str_contains($nombre, 'usuario')) $key = 'usuarios';
                                                                elseif (str_contains($nombre, 'paciente')) $key = 'pacientes';

                                                                $limitePaquete = $cat->pivot->cantidad_maxima ?? 0;
                                                                $limiteGlobal = $key ? ($limitesGlobales[$key] ?? 0) : 0;
                                                                $usados = $key ? ($currentCounts[$key] ?? 0) : 0;
                                                                $restante = max(0, $limiteGlobal - $usados);
                                                            @endphp
                                                            <div class="text-xs text-gray-600">
                                                                <span class="font-semibold capitalize">{{ $cat->nombre }}:</span>
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold ml-1">
                                                                    Límite paquete: {{ $limitePaquete }}
                                                                </span>
                                                                @if($key)
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 ml-1">
                                                                        Usados: {{ $usados }}/{{ $limiteGlobal }}
                                                                    </span>
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-50 text-green-700 ml-1">
                                                                        Faltan: {{ $restante }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $color = $paqueteVencido ? 'red' : match($sub->estatus_pago) {
                                                        'pagado' => 'green',
                                                        'pendiente' => 'yellow',
                                                        'fallido' => 'red',
                                                        default => 'gray',
                                                    };
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $color }}-100 text-{{ $color }}-800">
                                                    {{ $paqueteVencido ? 'Vencido' : ucfirst($sub->estatus_pago) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($sub->fecha_fin)
                                                    <span class="{{ $paqueteVencido ? 'text-red-600 font-semibold' : '' }}">
                                                        {{ \Carbon\Carbon::parse($sub->fecha_fin)->format('d/m/Y') }}
                                                    </span>
                                                    @if($paqueteVencido)
                                                        <i class="fas fa-exclamation-triangle text-red-600 ml-2" title="Paquete vencido"></i>
                                                    @endif
                                                @else
                                                    Indefinido
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @if($paqueteVencido)
                                                    <button type="button" @click="openRenewModal({{ $sub->id }})" class="inline-flex items-center px-3 py-1.5 rounded-md border border-[#0061F5] text-[#0061F5] hover:bg-[#0061F5] hover:text-white text-xs font-bold">
                                                        <i class="fas fa-sync-alt mr-2"></i> Renovar
                                                    </button>
                                                @elseif($sub->estatus_pago === 'pendiente' && $sub->metodo_pago === 'transferencia' && !$sub->comprobante_pago)
                                                    <button @click="openUploadModal({{ $sub->id }})" class="text-[#0061F5] hover:text-[#0051CC] font-bold inline-flex items-center">
                                                        <i class="fas fa-upload mr-1"></i> Subir Comprobante
                                                    </button>
                                                @elseif($sub->estatus_pago === 'pendiente' && $sub->comprobante_pago)
                                                    <span class="text-yellow-600 italic text-xs">Validando...</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-shopping-cart text-[#0061F5] mr-2"></i> Adquirir Nuevos Servicios
            </h2>

            @if($catalogos->isEmpty())
                <div class="bg-white rounded-lg shadow-sm p-8 text-center border border-gray-200">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-box-open text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">No hay productos disponibles</h3>
                    <p class="text-gray-500 mt-2">En este momento no hay items en el catálogo.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @if(!empty($suscripcionPaqueteVencida) && $suscripcionPaqueteVencida->paquete)
                        <div class="bg-red-50 rounded-xl shadow-md overflow-hidden border border-red-200 flex flex-col h-full ring-2 ring-red-300">
                            <div class="p-6 flex-grow">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-xl font-bold text-red-800 leading-tight">
                                        Renovar Paquete: {{ $suscripcionPaqueteVencida->paquete->nombre }}
                                    </h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Vencido
                                    </span>
                                </div>
                                <p class="text-3xl font-bold text-red-700 mb-4">${{ number_format($suscripcionPaqueteVencida->paquete->precio, 2) }}</p>
                                <p class="text-gray-700 text-sm leading-relaxed mb-4">Tu paquete venció. Renueva para continuar usando el sistema.</p>
                            </div>
                            <div class="p-6 bg-white border-t border-red-200 mt-auto">
                                <button type="button" @click.stop="openRenewModal({{ $suscripcionPaqueteVencida->id }})" class="w-full bg-red-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center shadow-sm">
                                    <i class="fas fa-sync-alt mr-2"></i> Renovar
                                </button>
                            </div>
                        </div>
                    @endif
                    @foreach($catalogos as $item)
                        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 flex flex-col h-full hover:shadow-lg transition-shadow duration-300">
                            <div class="p-6 flex-grow">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-xl font-bold text-gray-900 leading-tight">{{ $item->nombre }}</h3>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $item->tipo ?? 'Servicio' }}
                                    </span>
                                </div>
                                <p class="text-3xl font-bold text-[#0061F5] mb-4">${{ number_format($item->precio, 2) }}</p>
                                <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ $item->descripcion }}</p>
                            </div>
                            <div class="p-6 bg-gray-50 border-t border-gray-100 mt-auto">
                                <button 
                                    type="button"
                                    @click.stop="openModal({{ $item->id }})"
                                    class="w-full bg-[#0061F5] text-white font-bold py-3 px-4 rounded-lg hover:bg-[#0051CC] transition-colors flex items-center justify-center shadow-sm">
                                    <i class="fas fa-shopping-cart mr-2"></i> Adquirir
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Purchase Modal -->
        <div x-show="purchaseModalOpen" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <!-- Background backdrop, show/hide based on modal state. -->
            <div x-show="purchaseModalOpen" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="purchaseModalOpen" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         @click.away="purchaseModalOpen = false"
                         class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                        <form action="{{ route('compras.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-shopping-cart text-[#0061F5]"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Confirmar Compra
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Estás a punto de adquirir: <span class="font-bold text-gray-800" x-text="selectedItem ? selectedItem.nombre : ''"></span>
                                        </p>
                                        <input type="hidden" name="catalogo_id" x-bind:value="selectedItem ? selectedItem.id : ''">
                                        
                                        <!-- Cantidad -->
                                        <div class="mt-4">
                                            <label for="cantidad" class="block text-sm font-bold text-gray-700">Cantidad</label>
                                            <input type="number" name="cantidad" x-model="cantidad" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                        </div>

                                        <p class="mt-2 text-lg font-bold text-[#0061F5]">
                                            Total: $<span x-text="selectedItem ? (selectedItem.precio * cantidad).toFixed(2) : '0.00'"></span>
                                        </p>

                                        <!-- Método de Pago -->
                                        <div class="mt-4">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Método de Pago</label>
                                            <div class="space-y-2">
                                                <label class="inline-flex items-center w-full p-2 border rounded-md cursor-pointer hover:bg-gray-50" :class="{'border-[#0061F5] bg-blue-50': metodoPago === 'tarjeta'}">
                                                    <input type="radio" x-model="metodoPago" name="metodo_pago" value="tarjeta" class="form-radio text-[#0061F5] focus:ring-[#0061F5]">
                                                    <span class="ml-2 flex-grow">Tarjeta (Clip) - Activación Inmediata</span>
                                                    <i class="fas fa-credit-card text-gray-400"></i>
                                                </label>
                                                <label class="inline-flex items-center w-full p-2 border rounded-md cursor-pointer hover:bg-gray-50" :class="{'border-[#0061F5] bg-blue-50': metodoPago === 'transferencia'}">
                                                    <input type="radio" x-model="metodoPago" name="metodo_pago" value="transferencia" class="form-radio text-[#0061F5] focus:ring-[#0061F5]">
                                                    <span class="ml-2 flex-grow">Transferencia Bancaria (Requiere Validación)</span>
                                                    <i class="fas fa-university text-gray-400"></i>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Subir Comprobante -->
                                        <div class="mt-4 p-4 bg-yellow-50 rounded-md border border-yellow-200" x-show="metodoPago === 'transferencia'" x-transition>
                                            <h4 class="text-sm font-bold text-yellow-800 mb-2">Instrucciones para Transferencia:</h4>
                                            <p class="text-xs text-yellow-700 mb-2">Realiza la transferencia a la siguiente cuenta:</p>
                                            <ul class="text-xs text-yellow-700 list-disc list-inside mb-3">
                                                <li><strong>Banco:</strong> BBVA</li>
                                                <li><strong>CLABE:</strong> 012345678901234567</li>
                                                <li><strong>Concepto:</strong> <span x-text="'PAGO-' + (selectedItem ? selectedItem.nombre : '')"></span></li>
                                            </ul>
                                            <p class="text-xs text-yellow-800 font-semibold">
                                                <i class="fas fa-info-circle mr-1"></i> Al confirmar, se generará una orden pendiente. Deberás subir tu comprobante desde la lista de "Mis Suscripciones" para activar el servicio.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#0061F5] text-base font-medium text-white hover:bg-[#0051CC] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5] sm:ml-3 sm:w-auto sm:text-sm">
                                Confirmar y Pagar
                            </button>
                            <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="purchaseModalOpen = false">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Upload Receipt Modal -->
        <div x-show="uploadModalOpen" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div x-show="uploadModalOpen" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="uploadModalOpen" 
                         x-transition:enter="ease-out duration-300" 
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave="ease-in duration-200" 
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                         @click.away="uploadModalOpen = false"
                         class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                        <form :action="'{{ url('compras') }}/' + selectedSubscriptionId + '/comprobante'" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <i class="fas fa-upload text-[#0061F5]"></i>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            Subir Comprobante de Pago
                                        </h3>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-500 mb-4">
                                                Sube la imagen o PDF de tu transferencia para validar tu pago y activar el servicio.
                                            </p>
                                            
                                            <label for="comprobante_upload" class="block text-sm font-bold text-gray-700">Seleccionar Archivo</label>
                                            <input type="file" name="comprobante_pago" id="comprobante_upload" class="mt-1 block w-full text-sm text-gray-500
                                                file:mr-4 file:py-2 file:px-4
                                                file:rounded-md file:border-0
                                                file:text-sm file:font-semibold
                                                file:bg-[#E6F0FF] file:text-[#0061F5]
                                                hover:file:bg-[#CCE0FF]"
                                                required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#0061F5] text-base font-medium text-white hover:bg-[#0051CC] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0061F5] sm:ml-3 sm:w-auto sm:text-sm">
                                    Subir y Enviar
                                </button>
                                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="uploadModalOpen = false">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Renew Package Modal -->
        <div x-show="renewModalOpen" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
            <div x-show="renewModalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="renewModalOpen"
                         x-transition:enter="ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200"
                         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         @click.away="renewModalOpen = false"
                         class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                        <form action="{{ route('compras.renovar_paquete') }}" method="POST">
                            @csrf
                            <input type="hidden" name="suscripcion_anterior_id" x-bind:value="selectedRenewSubscriptionId">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                        <i class="fas fa-sync-alt text-red-600"></i>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                            Renovar Paquete
                                        </h3>
                                        <div class="mt-4">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Método de Pago</label>
                                            <div class="space-y-2">
                                                <label class="inline-flex items-center w-full p-2 border rounded-md cursor-pointer hover:bg-gray-50" :class="{'border-[#0061F5] bg-blue-50': metodoPagoRenovar === 'tarjeta'}">
                                                    <input type="radio" x-model="metodoPagoRenovar" name="metodo_pago" value="tarjeta" class="form-radio text-[#0061F5] focus:ring-[#0061F5]">
                                                    <span class="ml-2 flex-grow">Tarjeta (Clip) - Activación Inmediata</span>
                                                    <i class="fas fa-credit-card text-gray-400"></i>
                                                </label>
                                                <label class="inline-flex items-center w-full p-2 border rounded-md cursor-pointer hover:bg-gray-50" :class="{'border-[#0061F5] bg-blue-50': metodoPagoRenovar === 'transferencia'}">
                                                    <input type="radio" x-model="metodoPagoRenovar" name="metodo_pago" value="transferencia" class="form-radio text-[#0061F5] focus:ring-[#0061F5]">
                                                    <span class="ml-2 flex-grow">Transferencia Bancaria (Requiere Validación)</span>
                                                    <i class="fas fa-university text-gray-400"></i>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="mt-4 p-4 bg-yellow-50 rounded-md border border-yellow-200" x-show="metodoPagoRenovar === 'transferencia'" x-transition>
                                            <h4 class="text-sm font-bold text-yellow-800 mb-2">Instrucciones para Transferencia:</h4>
                                            <p class="text-xs text-yellow-700 mb-2">Realiza la transferencia a la siguiente cuenta:</p>
                                            <ul class="text-xs text-yellow-700 list-disc list-inside mb-3">
                                                <li><strong>Banco:</strong> BBVA</li>
                                                <li><strong>CLABE:</strong> 012345678901234567</li>
                                                <li><strong>Concepto:</strong> RENOVACION-PAQUETE</li>
                                            </ul>
                                            <p class="text-xs text-yellow-800 font-semibold">
                                                <i class="fas fa-info-circle mr-1"></i> Al confirmar, se generará una orden pendiente. Deberás subir tu comprobante desde la lista de "Mis Suscripciones".
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 sm:ml-3 sm:w-auto sm:text-sm">
                                    Renovar
                                </button>
                                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="renewModalOpen = false">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-admin-layout>
