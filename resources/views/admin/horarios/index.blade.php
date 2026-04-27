<x-admin-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Breadcrumbs -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ auth()->user()->hasRole('doctor') ? route('dashboard') : route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('horarios.breadcrumbs.index') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ auth()->user()->hasRole('doctor') ? __('horarios.titles.my_schedules') : __('horarios.titles.manage_by_doctor') }}
                    </h2>
                    <p class="text-gray-600 text-sm mt-1">
                        {{ auth()->user()->hasRole('doctor') ? __('horarios.descriptions.doctor') : __('horarios.descriptions.admin') }}
                    </p>
                </div>
                
                @if(!auth()->user()->hasRole('doctor'))
                <form action="{{ route('horarios.index') }}" method="GET" class="flex items-center">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" class="block w-64 p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-[#0061F5] focus:border-[#0061F5] placeholder-gray-400" placeholder="{{ __('horarios.search.placeholder_doctor') }}">
                    </div>
                    <button type="submit" class="ml-2 text-white bg-[#0061F5] hover:bg-[#0051CC] font-medium rounded-md text-sm px-4 py-2 shadow-sm">{{ __('common.buttons.search') }}</button>
                    @if(request('search'))
                        <a href="{{ route('horarios.index') }}" class="ml-2 text-gray-500 hover:text-gray-700 text-sm">{{ __('common.buttons.clear') }}</a>
                    @endif
                </form>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($users as $user)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="w-12 h-12 bg-[#E6F0FF] rounded-full flex items-center justify-center text-[#0061F5] font-bold text-xl mr-4">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg text-gray-900">{{ $user->name }} {{ $user->apellido_paterno }}</h3>
                                    <p class="text-sm text-gray-500">{{ $user->especialidad->nombre ?? __('horarios.empty.no_specialty') }}</p>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('horarios.sections.assigned_offices') }}</h4>
                                @forelse($user->consultorios as $consultorio)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm font-medium text-gray-700">{{ $consultorio->nombre }}</span>
                                        <form action="{{ route('horarios.manage') }}" method="GET">
                                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                                            <input type="hidden" name="consultorio_id" value="{{ $consultorio->id }}">
                                            <button type="submit" class="px-3 py-1 bg-white border border-[#0061F5] text-[#0061F5] text-xs font-bold rounded hover:bg-[#0061F5] hover:text-white transition-colors">
                                                {{ __('horarios.buttons.manage') }}
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400 italic">{{ __('horarios.empty.no_assigned_offices') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-8 text-center rounded-lg shadow-sm">
                        <p class="text-gray-500 text-lg">{{ __('horarios.empty.no_users_with_offices') }}</p>
                        <a href="{{ route('users.index') }}" class="text-[#0061F5] hover:underline mt-2 inline-block">{{ __('horarios.links.go_to_user_management') }}</a>
                    </div>
                @endforelse
            </div>
            
            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
