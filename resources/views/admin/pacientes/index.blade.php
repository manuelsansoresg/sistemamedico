<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('pacientes.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">{{ __('pacientes.title') }}</h2>
                        
                        <div class="flex items-center gap-2">
                            <form action="{{ route('pacientes.index') }}" method="GET" class="flex items-center">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <input type="text" name="search" value="{{ request('search') }}" class="block w-64 p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-md bg-gray-50 focus:ring-[#0061F5] focus:border-[#0061F5] placeholder-gray-400" placeholder="{{ __('common.buttons.search') }}...">
                                </div>
                                <button type="submit" class="ml-2 text-white bg-[#0061F5] hover:bg-[#0051CC] font-medium rounded-md text-sm px-4 py-2 shadow-sm">{{ __('common.buttons.search') }}</button>
                                @if(request('search'))
                                    <a href="{{ route('pacientes.index') }}" class="ml-2 text-gray-500 hover:text-gray-700 text-sm">{{ __('common.buttons.clear') }}</a>
                                @endif
                            </form>

                            <a href="{{ route('pacientes.create') }}" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm flex items-center justify-center whitespace-nowrap">
                                <i class="fas fa-plus mr-2"></i> {{ __('common.buttons.new') }}
                            </a>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.name') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.email') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('expedientes.columns.creation_date') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($pacientes as $paciente)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                            {{ $paciente->name }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                                            @if($paciente->curp)
                                                <div class="text-xs text-gray-400 font-normal mt-1">{{ $paciente->curp }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $paciente->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $paciente->created_at ? $paciente->created_at->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <a href="{{ route('pacientes.edit', $paciente) }}" class="inline-flex items-center justify-center w-9 h-9 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="{{ __('common.buttons.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('pacientes.destroy', $paciente) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('common.confirm_delete') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex cursor-pointer items-center justify-center w-9 h-9 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="{{ __('common.buttons.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                            {{ __('common.no_results') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $pacientes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
