<x-admin-layout>
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('branding.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ __('branding.title') }}</h2>
                            <p class="text-sm text-gray-500">{{ __('branding.description') }}</p>
                        </div>
                    </div>

                    <form action="{{ route('branding.update_logo') }}" method="POST" enctype="multipart/form-data" class="mt-6"
                        x-data="{
                            previewUrl: '{{ $configuracion->branding_logo_path ? asset('storage/'.$configuracion->branding_logo_path) : '' }}',
                            fileName: '',
                            dragging: false,
                            setFile(file) {
                                if (!file) return;
                                this.fileName = file.name;
                                this.previewUrl = URL.createObjectURL(file);
                                const dt = new DataTransfer();
                                dt.items.add(file);
                                this.$refs.logoInput.files = dt.files;
                            },
                            onSelect(e) {
                                const file = e && e.target && e.target.files ? e.target.files[0] : null;
                                this.setFile(file);
                            },
                            onDrop(e) {
                                this.dragging = false;
                                const file = e && e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files[0] : null;
                                this.setFile(file);
                            },
                        }">
                        @csrf

                        <div>
                            <label class="block text-sm font-bold text-gray-700">{{ __('branding.fields.logo') }}</label>

                            <div class="mt-2 flex items-start gap-6">
                                <div class="h-20 w-20 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200">
                                    <img x-show="previewUrl" :src="previewUrl" alt="{{ __('branding.fields.logo') }}" class="h-full w-full object-contain" style="display: none;">
                                    <i x-show="!previewUrl" class="fas fa-image text-gray-400 text-2xl"></i>
                                </div>

                                <div class="flex-1">
                                    <div class="rounded-lg border-2 border-dashed p-4 transition-colors"
                                        :class="dragging ? 'border-[#0061F5] bg-[#E6F0FF]' : 'border-gray-300 bg-white'"
                                        @dragover.prevent="dragging = true"
                                        @dragleave.prevent="dragging = false"
                                        @drop.prevent="onDrop($event)">
                                        <div class="flex flex-col gap-2">
                                            <p class="text-sm font-semibold text-gray-800">{{ __('common.drag_drop_image') }}</p>
                                            <p class="text-xs text-gray-500">{{ __('common.upload_image_hint') }}</p>
                                            <div>
                                                <button type="button" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors" @click="$refs.logoInput.click()">
                                                    {{ __('common.buttons.select_file') }}
                                                </button>
                                                <input type="file" name="logo" x-ref="logoInput" class="hidden" accept="image/jpeg,image/png,image/webp" @change="onSelect($event)">
                                            </div>
                                            <p class="text-xs text-gray-600" x-show="fileName" x-text="fileName"></p>
                                        </div>
                                    </div>
                                    @error('logo')
                                        <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button type="submit" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors">
                                {{ __('common.buttons.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
