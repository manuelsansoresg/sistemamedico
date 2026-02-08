<x-admin-layout>
    <div class="py-10" x-data="paqueteForm()">
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
                            <a href="{{ route('paquetes.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">Paquetes</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Editar</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-lg font-bold text-cyan-500 mb-6">Los campos marcados con * son requeridos</h2>

                    <form action="{{ route('paquetes.update', $paquete) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Columna Izquierda -->
                            <div class="space-y-6">
                                <!-- Nombre -->
                                <div>
                                    <label for="nombre" class="block text-sm font-bold text-gray-700 uppercase">*NOMBRE</label>
                                    <input type="text" name="nombre" id="nombre" value="{{ $paquete->nombre }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                </div>

                                <!-- Elementos (Catalogos) -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 uppercase mb-4">*Elementos</label>
                                    <div class="space-y-4">
                                        @foreach($catalogos as $catalogo)
                                            @php
                                                $related = $paquete->catalogos->find($catalogo->id);
                                                $isChecked = $related ? true : false;
                                                $maximo = $related ? $related->pivot->cantidad_maxima : '';
                                                $precio = $related ? $related->pivot->precio : $catalogo->precio;
                                            @endphp
                                            <div class="flex items-start" x-data="{ checked: {{ $isChecked ? 'true' : 'false' }} }">
                                                <div class="flex items-center h-5 mt-2">
                                                    <input 
                                                        type="checkbox" 
                                                        name="elementos[{{ $catalogo->id }}][checked]" 
                                                        value="1" 
                                                        x-model="checked"
                                                        @change="updateTotal()"
                                                        class="focus:ring-[#0061F5] h-4 w-4 text-[#0061F5] border-gray-300 rounded"
                                                    >
                                                </div>
                                                <div class="ml-3 w-full">
                                                    <label class="font-medium text-cyan-500">{{ $catalogo->nombre }}</label>
                                                    
                                                    <!-- Campos condicionales -->
                                                    <div x-show="checked" class="mt-2 grid grid-cols-2 gap-4 bg-gray-50 p-3 rounded-md">
                                                        <div>
                                                            <label class="block text-xs font-bold text-gray-600">Máximo:</label>
                                                            <input 
                                                                type="number" 
                                                                name="elementos[{{ $catalogo->id }}][cantidad_maxima]" 
                                                                value="{{ $maximo }}"
                                                                placeholder="Valor"
                                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] text-sm"
                                                                @input="updateTotal()"
                                                            >
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs font-bold text-gray-600">Precio:</label>
                                                            <input 
                                                                type="number" 
                                                                step="0.01" 
                                                                name="elementos[{{ $catalogo->id }}][precio]" 
                                                                value="{{ $precio }}"
                                                                class="package-item-price mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5] text-sm"
                                                                @input="updateTotal()"
                                                            >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <!-- Total Calculado -->
                                <div class="text-right mt-4">
                                    <span class="text-lg font-bold text-gray-800">Total: $<span x-text="total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})">0.00</span></span>
                                    <p class="text-xs text-gray-500 mt-1">Este texto informativo es la suma de los precios individualmente de los elementos del paquete</p>
                                </div>

                                <!-- Tipo -->
                                <div>
                                    <label for="tipo" class="block text-sm font-bold text-gray-700 uppercase">Tipo</label>
                                    <select name="tipo" id="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                        <option value="">Seleccione una opción</option>
                                        <option value="clinica" {{ $paquete->tipo == 'clinica' ? 'selected' : '' }}>Clínica</option>
                                        <option value="consultorio" {{ $paquete->tipo == 'consultorio' ? 'selected' : '' }}>Consultorio</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Columna Derecha -->
                            <div class="space-y-6">
                                <!-- Precio -->
                                <div>
                                    <label for="precio" class="block text-sm font-bold text-gray-700 uppercase">*PRECIO</label>
                                    <input type="number" step="0.01" name="precio" id="precio" value="{{ $paquete->precio }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                </div>

                                <!-- Porcentaje Ganancia (Oculto, forzado a 0) -->
                                <input type="hidden" name="porcentaje_ganancia" value="0">

                                <!-- Validar Cédula -->
                                <div>
                                    <label for="validar_cedula" class="block text-sm font-bold text-gray-700 uppercase">*VALIDAR CÉDULA</label>
                                    <div class="mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="validar_cedula" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" {{ $paquete->validar_cedula ? 'checked' : '' }}>
                                            <span class="ml-2 text-gray-600">SÍ</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Activo -->
                                <div>
                                    <label for="activo" class="block text-sm font-bold text-gray-700 uppercase">*ACTIVO</label>
                                    <div class="mt-2">
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="activo" class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" {{ $paquete->activo ? 'checked' : '' }}>
                                            <span class="ml-2 text-gray-600">Activo</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-8">
                            <x-primary-button>
                                Guardar
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('paqueteForm', () => ({
                total: 0,
                init() {
                    this.updateTotal();
                },
                updateTotal() {
                    let sum = 0;
                    document.querySelectorAll('input[type="checkbox"][name^="elementos"]:checked').forEach(checkbox => {
                        const container = checkbox.closest('.flex.items-start');
                        const priceInput = container.querySelector('.package-item-price');
                        const quantityInput = container.querySelector('input[name*="[cantidad_maxima]"]');
                        
                        if (priceInput) {
                            const price = parseFloat(priceInput.value) || 0;
                            let quantity = 0;
                            
                            if (quantityInput) {
                                const val = parseFloat(quantityInput.value);
                                quantity = isNaN(val) ? 0 : val;
                            }
                            
                            sum += price * quantity;
                        }
                    });
                    this.total = sum;
                }
            }))
        })
    </script>
</x-admin-layout>
