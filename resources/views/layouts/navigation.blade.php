<nav x-data="{ open: false }" class="bg-transparent">
    <div class="w-full bg-[#27ADFA] h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="w-24"></div>
        <div class="flex-grow text-center">
            <span class="text-white font-bold text-2xl tracking-widest">{{ config('app.name', 'Sistema Médico') }}</span>
        </div>
        <div class="w-24 flex justify-end items-center space-x-4">
            <span class="text-white font-medium">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-white hover:text-gray-200 transition-colors cursor-pointer" title="Cerrar sesión">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </button>
            </form>
        </div>
    </div>
</nav>
