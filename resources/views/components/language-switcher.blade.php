@php
    $locale = app()->getLocale();
@endphp

<div class="inline-flex items-center rounded-full bg-gray-100 border border-gray-200 p-1">
    <a href="{{ route('lang.switch', 'en') }}"
       class="{{ $locale === 'en' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }} px-3 py-1 rounded-full text-xs font-bold tracking-wide transition-colors">
        EN
    </a>
    <a href="{{ route('lang.switch', 'es') }}"
       class="{{ $locale === 'es' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }} px-3 py-1 rounded-full text-xs font-bold tracking-wide transition-colors">
        ES
    </a>
</div>
