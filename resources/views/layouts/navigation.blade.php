<nav x-data="{ open: false }" class="bg-[#27ADFA]">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-14 sm:h-16 flex items-center gap-2 sm:gap-3">
        <div class="flex flex-none items-center min-w-0">
            <img src="{{ Auth::user()->profile_photo_url }}"
                 alt="{{ Auth::user()->name }}"
                 class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg object-cover border border-white/40 shrink-0">
        </div>

        <div class="flex-1 min-w-0 flex items-center">
            <a href="{{ route('dashboard') }}" class="truncate text-white font-bold text-base sm:text-lg tracking-tight leading-tight">
                {{ config('app.name', 'Sistema Medico') }}
            </a>
        </div>

        <div class="flex flex-none items-center justify-end gap-1.5 sm:gap-3">
            <x-language-switcher />
            <x-notification-center />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full text-white hover:bg-white/15 transition-colors" title="{{ __('common.log_out') }}">
                    <i class="fas fa-sign-out-alt text-base sm:text-lg" style="color: #ffffff;"></i>
                </button>
            </form>
        </div>
    </div>
</nav>
