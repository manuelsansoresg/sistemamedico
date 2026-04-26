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

            <h1 class="text-2xl font-bold text-[#1E293B] mb-8">Gestión de Suscripciones</h1>

            @php
                $limitesGlobales = app(\App\Services\SubscriptionService::class)->calculateLimits(auth()->user());
                $currentCounts = [
                    'clinicas' => \App\Models\Clinica::where('created_by', auth()->id())->count(),
                    'consultorios' => \App\Models\Consultorio::where('created_by', auth()->id())->count(),
                    'usuarios' => \App\Models\User::where('created_by', auth()->id())->whereHas('roles', function ($q) { $q->whereIn('name', ['asistente','secretaria']); })->count(),
                    'pacientes' => \App\Models\User::role('paciente')->where('created_by', auth()->id())->count(),
                ];
            @endphp

            <!-- SECCIÓN 1: Mis Planes Activos -->
            <div class="mb-12">
                @php
                    $suscripcionesActivasCount = $suscripciones->getCollection()->filter(function ($sub) {
                        if ($sub->estatus_pago !== 'pagado') {
                            return false;
                        }

                        if (! $sub->fecha_fin) {
                            return true;
                        }

                        return \Carbon\Carbon::parse($sub->fecha_fin)->gte(\Carbon\Carbon::now());
                    })->count();
                @endphp

                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-[#1E293B] flex items-center">
                        <i class="fas fa-layer-group text-[#0061F5] mr-2"></i> Mis Planes Activos
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#27ADFA]/15 text-[#0061F5]">
                        {{ $suscripcionesActivasCount }} activos
                    </span>
                </div>

                @if($suscripciones->isEmpty())
                    <div class="bg-white rounded-xl shadow-sm p-8 text-center border border-gray-200">
                        <div class="text-gray-400 mb-3">
                            <i class="fas fa-box-open text-5xl"></i>
                        </div>
                        <p class="text-gray-500 text-lg">No tienes suscripciones activas.</p>
                        <p class="text-gray-400 text-sm mt-1">Adquiere un servicio en la sección de abajo.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($suscripciones as $sub)
                            @php
                                $paqueteVencido = $sub->tipo === 'paquete'
                                    && $sub->estatus_pago === 'pagado'
                                    && $sub->fecha_fin
                                    && \Carbon\Carbon::parse($sub->fecha_fin)->lt(\Carbon\Carbon::now());

                                $estadoLabel = $paqueteVencido ? 'VENCIDO' : strtoupper($sub->estatus_pago ?? '—');
                                $estadoClasses = $paqueteVencido
                                    ? 'bg-red-100 text-red-800'
                                    : match($sub->estatus_pago) {
                                        'pagado' => 'bg-green-100 text-green-800',
                                        'pendiente' => 'bg-yellow-100 text-yellow-800',
                                        'fallido' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-[#1E293B]',
                                    };

                                $serviceName = $sub->tipo === 'paquete'
                                    ? (optional($sub->paquete)->nombre ?? 'Paquete Eliminado')
                                    : (optional($sub->catalogo)->nombre ?? 'Servicio Eliminado');

                                $iconClass = 'fas fa-box';
                                $lowerName = strtolower($serviceName);
                                if ($sub->tipo === 'paquete') {
                                    $iconClass = 'fas fa-cube';
                                } elseif (str_contains($lowerName, 'paciente')) {
                                    $iconClass = 'fas fa-user';
                                } elseif (str_contains($lowerName, 'consultorio')) {
                                    $iconClass = 'fas fa-hospital';
                                } elseif (str_contains($lowerName, 'clínica') || str_contains($lowerName, 'clinica')) {
                                    $iconClass = 'fas fa-clinic-medical';
                                } elseif (str_contains($lowerName, 'usuario')) {
                                    $iconClass = 'fas fa-users';
                                }

                                $metricas = [];

                                if ($sub->tipo === 'paquete' && $sub->paquete) {
                                    foreach ($sub->paquete->catalogos as $cat) {
                                        $total = (int) ($cat->pivot->cantidad_maxima ?? 0);
                                        if ($total <= 0) {
                                            continue;
                                        }

                                        $nombre = strtolower($cat->nombre);
                                        $key = null;
                                        $label = $cat->nombre;

                                        if (str_contains($nombre, 'clínica') || str_contains($nombre, 'clinica')) {
                                            $key = 'clinicas';
                                            $label = 'Clínicas';
                                        } elseif (str_contains($nombre, 'consultorio')) {
                                            $key = 'consultorios';
                                            $label = 'Consultorios';
                                        } elseif (str_contains($nombre, 'usuario')) {
                                            $key = 'usuarios';
                                            $label = 'Usuarios';
                                        } elseif (str_contains($nombre, 'paciente')) {
                                            $key = 'pacientes';
                                            $label = 'Pacientes';
                                        }

                                        $used = (int) ($key ? ($currentCounts[$key] ?? 0) : 0);
                                        $percent = $total > 0 ? min(100, (int) round(($used / $total) * 100)) : 0;

                                        $metricas[] = [
                                            'label' => $label,
                                            'used' => $used,
                                            'total' => $total,
                                            'percent' => $percent,
                                        ];
                                    }
                                } elseif ($sub->tipo === 'individual' && $sub->catalogo) {
                                    $total = (int) ($sub->cantidad ?? 1);
                                    $nombre = strtolower($sub->catalogo->nombre);
                                    $key = null;
                                    $label = $sub->catalogo->nombre;
                                    $used = 0;

                                    if (str_contains($nombre, 'clínica') || str_contains($nombre, 'clinica')) {
                                        $key = 'clinicas';
                                        $label = 'Clínicas';
                                        $used = (int) ($currentCounts['clinicas'] ?? 0);
                                        $total = (int) ($limitesGlobales['clinicas'] ?? $total);
                                    } elseif (str_contains($nombre, 'consultorio')) {
                                        $key = 'consultorios';
                                        $label = 'Consultorios';
                                        $used = (int) ($currentCounts['consultorios'] ?? 0);
                                        $total = (int) ($limitesGlobales['consultorios'] ?? $total);
                                    } elseif (str_contains($nombre, 'usuario')) {
                                        $key = 'usuarios';
                                        $label = 'Usuarios';
                                        $used = (int) ($currentCounts['usuarios'] ?? 0);
                                        $total = (int) ($limitesGlobales['usuarios'] ?? $total);
                                    } elseif (str_contains($nombre, 'paciente')) {
                                        $key = 'pacientes';
                                        $label = 'Pacientes';
                                        $used = (int) ($sub->pacientes_count ?? 0);
                                    }

                                    if ($total > 0) {
                                        $percent = min(100, (int) round(($used / $total) * 100));
                                        $metricas[] = [
                                            'label' => $label,
                                            'used' => $used,
                                            'total' => $total,
                                            'percent' => $percent,
                                        ];
                                    }
                                }
                            @endphp

                            <div class="bg-white rounded-xl border border-gray-200 p-6 hover:border-[#27ADFA]/60 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg bg-[#27ADFA]/15 flex items-center justify-center text-[#0061F5]">
                                            <i class="{{ $iconClass }}"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-bold text-[#1E293B]">{{ $serviceName }}</h3>
                                            <div class="text-xs text-[#64748b]">Suscripción anual</div>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold {{ $estadoClasses }}">
                                        {{ $estadoLabel }}
                                    </span>
                                </div>

                                @if(!empty($metricas))
                                    <div class="mt-4 space-y-3">
                                        @foreach($metricas as $m)
                                            @php
                                                $widthClass = match (true) {
                                                    $m['percent'] <= 0 => 'w-0',
                                                    $m['percent'] < 25 => 'w-1/4',
                                                    $m['percent'] < 50 => 'w-1/2',
                                                    $m['percent'] < 75 => 'w-3/4',
                                                    default => 'w-full',
                                                };
                                            @endphp
                                            <div>
                                                <div class="flex items-center justify-between text-xs text-[#64748b]">
                                                    <span>{{ $m['label'] }}</span>
                                                    <span class="tabular-nums">{{ $m['used'] }}/{{ $m['total'] }}</span>
                                                </div>
                                                <div class="mt-1 h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                                                    <div class="h-2 rounded-full bg-[#27ADFA] {{ $widthClass }}"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                                    <div class="text-xs text-[#64748b]">
                                        @if($sub->fecha_fin)
                                            Próxima renovación: {{ \Carbon\Carbon::parse($sub->fecha_fin)->format('d/m/Y') }}
                                        @else
                                            Vigencia: Indefinida
                                        @endif
                                    </div>

                                    <div class="shrink-0">
                                        @if($paqueteVencido)
                                            <button type="button" @click="openRenewModal({{ $sub->id }})" class="inline-flex items-center px-3 py-1.5 rounded-md bg-[#0061F5] text-white hover:bg-[#0051CC] text-xs font-bold transition-colors">
                                                Renovar
                                            </button>
                                        @elseif($sub->estatus_pago === 'pendiente' && $sub->metodo_pago === 'transferencia' && !$sub->comprobante_pago)
                                            <button type="button" @click="openUploadModal({{ $sub->id }})" class="inline-flex items-center px-3 py-1.5 rounded-md bg-[#0061F5] text-white hover:bg-[#0051CC] text-xs font-bold transition-colors">
                                                Subir comprobante
                                            </button>
                                        @elseif($sub->estatus_pago === 'pendiente' && $sub->comprobante_pago)
                                            <span class="text-yellow-700 text-xs font-bold">Validando…</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(method_exists($suscripciones, 'links'))
                        <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="text-sm text-gray-500">
                                Mostrando {{ $suscripciones->firstItem() }} a {{ $suscripciones->lastItem() }} de {{ $suscripciones->total() }} registros
                            </div>
                            <div>
                                {{ $suscripciones->links() }}
                            </div>
                        </div>
                    @endif
                @endif
            </div>

            <!-- SEPARADOR VISUAL OBLIGATORIO -->
            <div class="mt-[48px] pt-[48px] border-t-2 border-[#e2e8f0]">
                <div class="flex items-start justify-between gap-6 mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-[#1E293B] flex items-center">
                            <i class="fas fa-shopping-bag text-[#0061F5] mr-2"></i> Tienda de Servicios
                        </h2>
                        <p class="text-sm text-[#64748b] mt-1">Expande las capacidades de tu centro médico con nuevos módulos.</p>
                    </div>
                    <div class="shrink-0">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-[#F8FAFC] border border-[#e2e8f0] text-[#1E293B]">
                            Anual
                        </span>
                    </div>
                </div>

                @if($catalogos->isEmpty())
                    <div class="bg-[#f8fafc] rounded-[16px] p-8 text-center border-2 border-dashed border-[#cbd5e1]">
                        <div class="text-gray-400 mb-3">
                            <i class="fas fa-box-open text-5xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-[#1E293B]">No hay productos disponibles</h3>
                        <p class="text-[#64748b] mt-1">En este momento no hay items en el catálogo.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @if(!empty($suscripcionPaqueteVencida) && $suscripcionPaqueteVencida->paquete)
                            <div class="rounded-[16px] border-2 border-dashed border-red-300 bg-red-50 p-6 flex flex-col h-full hover:border-red-500 transition-colors shadow-sm">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="h-10 w-10 rounded-lg bg-red-100 flex items-center justify-center text-red-600 shrink-0 mr-3">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-[#1E293B]">{{ $suscripcionPaqueteVencida->paquete->nombre }}</h3>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 shrink-0 ml-2">
                                        Vencido
                                    </span>
                                </div>
                                <p class="text-2xl font-bold text-[#1E293B] mb-1">${{ number_format($suscripcionPaqueteVencida->paquete->precio, 2) }}</p>
                                <div class="text-xs text-[#64748b] mb-3">Suscripción anual</div>
                                <p class="text-sm text-[#64748b] flex-grow">Tu paquete anterior venció. Renueva para continuar usando el sistema.</p>
                                <button type="button" @click.stop="openRenewModal({{ $suscripcionPaqueteVencida->id }})" class="mt-4 w-full inline-flex items-center justify-center px-4 py-2.5 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 transition-colors text-sm">
                                    Renovar &rarr;
                                </button>
                            </div>
                        @endif

                        @foreach($catalogos as $item)
                            @php
                                $lowerName = strtolower($item->nombre);
                                if (str_contains($lowerName, 'paciente')) $iconClass = 'fas fa-user-injured';
                                elseif (str_contains($lowerName, 'consultorio')) $iconClass = 'fas fa-stethoscope';
                                elseif (str_contains($lowerName, 'clinica') || str_contains($lowerName, 'clínica')) $iconClass = 'fas fa-hospital';
                                elseif (str_contains($lowerName, 'usuario')) $iconClass = 'fas fa-user-cog';
                                else $iconClass = 'fas fa-cube';
                            @endphp
                            <div class="rounded-[16px] border-2 border-dashed border-[#cbd5e1] bg-[#F8FAFC] p-6 flex flex-col h-full hover:border-[#0061F5] transition-colors shadow-sm">
                                <div class="flex items-start mb-3">
                                    <div class="h-10 w-10 rounded-lg bg-[#27ADFA]/15 flex items-center justify-center text-[#0061F5] shrink-0 mr-3">
                                        <i class="{{ $iconClass }}"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-lg font-bold text-[#1E293B]">{{ $item->nombre }}</h3>
                                        <div class="text-xs text-[#64748b] mt-1">Suscripción anual</div>
                                    </div>
                                </div>
                                <div class="flex items-end gap-2 mb-3">
                                    <p class="text-2xl font-bold text-[#1E293B]">${{ number_format($item->precio, 2) }}</p>
                                    <span class="text-xs text-[#64748b] font-semibold mb-1">/anual</span>
                                </div>
                                <p class="text-sm text-[#64748b] flex-grow">{{ Str::words($item->descripcion ?: 'Adquiere este servicio para ampliar las capacidades de tu sistema.', 15) }}</p>
                                <button type="button" @click.stop="openModal({{ $item->id }})" class="mt-4 w-full inline-flex items-center justify-center px-4 py-2.5 bg-[#0061F5] text-white font-bold rounded-lg hover:bg-[#0051CC] transition-colors text-sm">
                                    Adquirir
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Purchase Modal -->
        <div x-show="purchaseModalOpen" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
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
                                            Estás a punto de adquirir: <span class="font-bold text-[#1E293B]" x-text="selectedItem ? selectedItem.nombre : ''"></span>
                                        </p>
                                        <input type="hidden" name="catalogo_id" x-bind:value="selectedItem ? selectedItem.id : ''">
                                        
                                        <div class="mt-4">
                                            <label for="cantidad" class="block text-sm font-bold text-gray-700">Cantidad</label>
                                            <input type="number" name="cantidad" x-model="cantidad" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                        </div>

                                        <p class="mt-2 text-lg font-bold text-[#0061F5]">
                                            Total anual: $<span x-text="selectedItem ? (selectedItem.precio * cantidad).toFixed(2) : '0.00'"></span>
                                            <span class="text-sm text-gray-500 font-normal">/anual</span>
                                        </p>

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
</x-admin-layout>
