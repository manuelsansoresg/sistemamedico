<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <img src="{{ Auth::user()->profile_photo_url }}"
                 alt="{{ Auth::user()->name }}"
                 class="w-12 h-12 rounded-full object-cover">
            <div class="min-w-0 leading-tight">
                <div class="text-sm font-bold text-gray-900 uppercase">{{ Auth::user()->name }}</div>
                <div class="text-xs text-gray-500">
                    {{ Auth::user()->especialidad?->nombre ?? __('common.roles.'.(Auth::user()->roles->first()?->name ?? 'user')) }}
                </div>
            </div>
        </div>

        <div class="flex-1 flex justify-center px-4">
            <a href="{{ route('dashboard') }}" class="text-gray-900 font-bold text-xl md:text-2xl tracking-wide">
                {{ config('app.name', 'Sistema Medico') }}
            </a>
        </div>

        <div class="flex items-center gap-4">
            <x-language-switcher />
            <div class="h-6 w-px bg-gray-200"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-gray-500 hover:text-gray-700 font-semibold uppercase tracking-wide transition-colors">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="hidden sm:inline">{{ __('common.log_out') }}</span>
                </button>
            </form>
        </div>
    </div>
</nav>
