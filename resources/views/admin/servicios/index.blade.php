<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('servicios.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">{{ __('servicios.title') }}</h2>
                        <a href="{{ route('servicios.create') }}" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm flex items-center">
                            <i class="fas fa-plus mr-2"></i> {{ __('common.buttons.new') }}
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('servicios.columns.name') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('servicios.columns.duration') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('servicios.columns.cost') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($servicios as $servicio)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{ $servicio->nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $servicio->duracion }} min</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($servicio->costo, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <a href="{{ route('servicios.edit', $servicio) }}" class="inline-flex items-center justify-center w-9 h-9 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="{{ __('common.buttons.edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('servicios.destroy', $servicio) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('common.confirm_delete') }}');">
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
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            {{ __('common.no_results') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $servicios->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
