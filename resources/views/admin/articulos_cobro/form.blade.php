<div class="grid grid-cols-1 gap-6">
    <div>
        <label for="nombre" class="block text-sm font-bold text-gray-700">{{ __('cobros.fields.article') }}</label>
        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $articulo?->nombre) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
    </div>
    <div>
        <label for="descripcion" class="block text-sm font-bold text-gray-700">{{ __('cobros.fields.description') }}</label>
        <textarea name="descripcion" id="descripcion" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">{{ old('descripcion', $articulo?->descripcion) }}</textarea>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="unidad" class="block text-sm font-bold text-gray-700">{{ __('cobros.fields.unit') }}</label>
            <input type="text" name="unidad" id="unidad" value="{{ old('unidad', $articulo?->unidad) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]">
        </div>
        <div>
            <label for="precio" class="block text-sm font-bold text-gray-700">{{ __('cobros.fields.catalog_price') }}</label>
            <input type="number" name="precio" id="precio" value="{{ old('precio', $articulo?->precio) }}" min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]" required>
        </div>
    </div>
    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="activo" value="0">
        <input type="checkbox" name="activo" value="1" class="rounded border-gray-300 text-[#0061F5] focus:ring-[#0061F5]" {{ old('activo', $articulo?->activo ?? true) ? 'checked' : '' }}>
        {{ __('status.active') }}
    </label>
</div>
<div class="flex justify-end mt-6">
    <a href="{{ route('articulos-cobro.index') }}" class="px-4 py-2 bg-gray-500 text-white font-bold rounded-md hover:bg-gray-600 transition-colors mr-2">{{ __('common.buttons.cancel') }}</a>
    <x-primary-button>{{ __('common.buttons.save') }}</x-primary-button>
</div>
