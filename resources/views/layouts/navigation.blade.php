<nav x-data="{ open: false }" class="bg-[#27ADFA]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center gap-4">
        <div class="flex items-center gap-3 min-w-0">
            <img src="{{ Auth::user()->profile_photo_url }}"
                 alt="{{ Auth::user()->name }}"
                 class="w-12 h-12 rounded-full object-cover border-2 border-white/60">
            <div class="min-w-0 leading-tight">
                <div class="text-sm font-bold text-white uppercase">{{ Auth::user()->name }}</div>
                <div class="text-xs text-white/70">
                    {{ Auth::user()->especialidad?->nombre ?? __('common.roles.'.(Auth::user()->roles->first()?->name ?? 'user')) }}
                </div>
            </div>
        </div>

        <div class="flex-1 flex justify-center px-4">
            <a href="{{ route('dashboard') }}" class="text-white font-bold text-xl md:text-2xl tracking-wide">
                {{ config('app.name', 'Sistema Medico') }}
            </a>
        </div>

        <div class="flex items-center gap-4">
            <x-language-switcher />
            <div class="h-6 w-px bg-white/30"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-white/80 hover:text-white font-semibold uppercase tracking-wide transition-colors">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="hidden sm:inline">{{ __('common.log_out') }}</span>
                </button>
            </form>
        </div>
    </div>
</nav>
