<x-admin-layout>
    <div class="py-12">
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
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.suscripciones.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            Suscripciones
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Nueva Suscripción</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6 text-[#0061F5]">Nueva Suscripción</h2>

                    <form action="{{ route('admin.suscripciones.store') }}" method="POST" enctype="multipart/form-data" x-data="{ 
                        tipo: 'paquete',
                        metodo_pago: 'tarjeta'
                    }">
                        @csrf
                        
                        <!-- Usuario -->
                        <div class="mb-6">
                            <label for="user_id" class="block text-sm font-bold text-gray-700">Doctor</label>
                            <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                <option value="">-- Seleccionar Doctor --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} {{ $user->apellido_paterno }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tipo de Compra -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tipo de Suscripción</label>
                            <div class="flex items-center space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" x-model="tipo" name="tipo" value="paquete" class="form-radio text-[#0061F5] focus:ring-[#0061F5]">
                                    <span class="ml-2">Paquete</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" x-model="tipo" name="tipo" value="individual" class="form-radio text-[#0061F5] focus:ring-[#0061F5]">
                                    <span class="ml-2">Catálogo (Individual)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Selección de Item -->
                        <div class="mb-6">
                            <label for="item_id" class="block text-sm font-bold text-gray-700" x-text="tipo === 'paquete' ? 'Seleccionar Paquete' : 'Seleccionar Item del Catálogo'"></label>
                            
                            <!-- Select Paquetes -->
                            <select name="item_id" id="paquete_select" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" 
                                x-show="tipo === 'paquete'" 
                                x-bind:disabled="tipo !== 'paquete'" 
                                x-bind:required="tipo === 'paquete'">
                                <option value="">-- Seleccionar Paquete --</option>
                                @foreach($paquetes as $paquete)
                                    <option value="{{ $paquete->id }}">{{ $paquete->nombre }} - ${{ number_format($paquete->precio, 2) }}</option>
                                @endforeach
                            </select>

                            <!-- Select Catalogo -->
                            <select name="item_id" id="catalogo_select" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" 
                                x-show="tipo === 'individual'" 
                                x-bind:disabled="tipo !== 'individual'" 
                                x-bind:required="tipo === 'individual'" 
                                style="display: none;">
                                <option value="">-- Seleccionar Item --</option>
                                @foreach($catalogos as $catalogo)
                                    <option value="{{ $catalogo->id }}">{{ $catalogo->nombre }} - ${{ number_format($catalogo->precio, 2) }}</option>
                                @endforeach
                            </select>
                            
                            <!-- Cantidad (Solo para Catalogo) -->
                            <div class="mt-4" x-show="tipo === 'individual'" style="display: none;">
                                <label for="cantidad" class="block text-sm font-bold text-gray-700">Cantidad</label>
                                <input type="number" name="cantidad" value="1" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" 
                                    x-bind:disabled="tipo !== 'individual'" 
                                    x-bind:required="tipo === 'individual'">
                            </div>
                        </div>

                        <!-- Método de Pago -->
                        <div class="mb-6">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Método de Pago</label>
                            <select name="metodo_pago" x-model="metodo_pago" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
                                <option value="tarjeta">Tarjeta (Clip)</option>
                                <option value="transferencia">Transferencia Bancaria</option>
                            </select>
                        </div>

                        <!-- Comprobante (Solo si es transferencia) -->
                        <div class="mb-6" x-show="metodo_pago === 'transferencia'" x-transition>
                            <label for="comprobante_pago" class="block text-sm font-bold text-gray-700">Subir Comprobante de Pago</label>
                            <input type="file" name="comprobante_pago" id="comprobante_pago" class="mt-1 block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-[#E6F0FF] file:text-[#0061F5]
                                hover:file:bg-[#CCE0FF]">
                            <p class="text-xs text-gray-500 mt-1">Formatos: PDF, JPG, PNG. Máx: 2MB.</p>
                        </div>

                        <div class="flex justify-end mt-6">
                            <a href="{{ route('admin.suscripciones.index') }}" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors mr-2">Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors">Crear Suscripción</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
