@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge([
    'class' => 'mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-[#0061F5] focus:ring-[#0061F5]'
]) }}>
