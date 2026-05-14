<x-guest-layout maxWidth="sm:max-w-2xl">
    <x-slot name="header">
        <div class="py-6 text-center" style="background-color: #003366;">
            <h1 class="text-2xl font-bold text-white tracking-wider">
                {{ config('app.name', 'Sistema Médico') }}
            </h1>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold" style="color: #003366;">
                {{ __('public.receipt.upload_title') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                {!! __('public.receipt.package_amount', ['package' => '<strong>'.e($suscripcion->paquete->nombre).'</strong>', 'amount' => '<strong>$'.number_format($suscripcion->precio, 2).'</strong>']) !!}
            </p>
        </div>

                @if(session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    {{ session('error') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($suscripcion->comprobante_pago)
                     <div class="mb-8 p-4 bg-blue-50 rounded-md border border-blue-100">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h3 class="font-bold text-blue-800">{{ __('public.receipt.received_title') }}</h3>
                                <p class="text-sm text-blue-600">{{ __('public.receipt.received_message') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('suscripciones.guardar_comprobante', $token) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div x-data="{ isDropping: false, fileName: '' }" class="w-full">
                        <label class="block text-sm font-medium text-gray-700">
                            {{ __('public.receipt.select_label') }}
                        </label>
                        <div 
                            @dragover.prevent="isDropping = true"
                            @dragleave.prevent="isDropping = false"
                            @drop.prevent="isDropping = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0].name"
                            :class="{'border-blue-500 bg-blue-50': isDropping, 'border-gray-300': !isDropping}"
                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md hover:border-blue-800 transition-colors"
                        >
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="comprobante" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-800 hover:text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>{{ __('public.receipt.upload_file') }}</span>
                                        <input 
                                            x-ref="fileInput"
                                            @change="fileName = $refs.fileInput.files[0].name"
                                            id="comprobante" 
                                            name="comprobante" 
                                            type="file" 
                                            class="sr-only" 
                                            accept="application/pdf,image/png,image/jpeg,image/jpg"
                                        >
                                    </label>
                                    <p class="pl-1">{{ __('public.receipt.drag_drop') }}</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    {{ __('public.receipt.file_hint') }}
                                </p>
                                <p x-show="fileName" x-text="fileName" class="text-sm text-green-600 font-medium mt-2"></p>
                            </div>
                        </div>
                        @error('comprobante')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" style="background-color: #003366;">
                            {{ __('public.receipt.submit') }}
                        </button>
                    </div>
                </form>
    </div>
</x-guest-layout>
