<x-admin-layout>
    <div class="py-10" x-data="{ transfer: false }">
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
                            <a href="{{ route('configuraciones.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">Configuraciones</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Crear</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form action="{{ route('configuraciones.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Columna Izquierda -->
                            <div class="space-y-6">
                                <!-- Usuario -->
                                <div>
                                    <label for="user_id" class="block text-sm font-bold text-gray-700">Usuario</label>
                                    <select name="user_id" id="user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                        <option value="">Seleccione un usuario</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Aceptar transferencia bancaria -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Aceptar transferencia bancaria</label>
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            name="aceptar_transferencia_bancaria" 
                                            value="1" 
                                            x-model="transfer"
                                            class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]"
                                            {{ old('aceptar_transferencia_bancaria') ? 'checked' : '' }}
                                        >
                                        <span class="ml-2 text-[#0061F5] font-medium">Sí</span>
                                    </div>
                                </div>

                                <!-- Campos condicionales de banco -->
                                <div x-show="transfer" x-transition class="space-y-4">
                                    <div>
                                        <label for="banco" class="block text-sm font-bold text-gray-700">Banco</label>
                                        <input type="text" name="banco" id="banco" value="{{ old('banco') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                    </div>

                                    <div>
                                        <label for="titular" class="block text-sm font-bold text-gray-700">Titular</label>
                                        <input type="text" name="titular" id="titular" value="{{ old('titular') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                    </div>

                                    <div>
                                        <label for="cuenta" class="block text-sm font-bold text-gray-700">Cuenta</label>
                                        <input type="text" name="cuenta" id="cuenta" value="{{ old('cuenta') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                    </div>

                                    <div>
                                        <label for="clabe" class="block text-sm font-bold text-gray-700">CLABE</label>
                                        <input type="text" name="clabe" id="clabe" value="{{ old('clabe') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
                                    </div>
                                </div>
                            </div>

                            <!-- Columna Derecha -->
                            <div class="space-y-6">
                                <!-- Aceptar pagos con tarjeta -->
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-2">Aceptar pagos con tarjeta</label>
                                    <div class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            name="aceptar_pagos_con_tarjeta" 
                                            value="1" 
                                            class="rounded border-gray-300 text-[#0061F5] shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]"
                                            {{ old('aceptar_pagos_con_tarjeta') ? 'checked' : '' }}
                                        >
                                        <span class="ml-2 text-[#0061F5] font-medium">Sí</span>
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

    <div id="configTransferInit" data-transfer="{{ old('aceptar_transferencia_bancaria') ? '1' : '0' }}" class="hidden"></div>

    <script>
        document.addEventListener('alpine:init', () => {
            const el = document.getElementById('configTransferInit');
            const shouldTransfer = el && el.dataset.transfer === '1';
            if (shouldTransfer) {
                Alpine.store('transfer', true);
            }
        });
    </script>
</x-admin-layout>
