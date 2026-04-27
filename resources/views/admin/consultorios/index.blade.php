<x-admin-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('dashboard.title') }}
                        </a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('consultorios.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">{{ __('consultorios.title') }}</h2>
                        <a href="{{ route('consultorios.create') }}" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm flex items-center">
                            <i class="fas fa-plus mr-2"></i> {{ __('common.buttons.new') }}
                        </a>
                    </div>

                    <x-table>
                        @php
                            $confirmDeleteRecord = __('common.confirm_delete_record');
                        @endphp
                        <x-slot:header>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.phone') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.created_by') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.actions') }}</th>
                            </tr>
                        </x-slot:header>
                        <x-slot:body>
                            @forelse ($consultorios as $consultorio)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">{{ $consultorio->nombre }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $consultorio->telefono }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $consultorio->activo ? __('status.active') : __('status.inactive') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $consultorio->creator->name ?? __('common.none') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end items-center space-x-2">
                                            <a href="{{ route('consultorios.edit', $consultorio) }}" class="inline-flex items-center justify-center w-9 h-9 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="{{ __('common.buttons.edit') }}">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('consultorios.destroy', $consultorio) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ $confirmDeleteRecord }}');">
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
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                        {{ __('common.no_results') }}
                                    </td>
                                </tr>
                            @endforelse
                        </x-slot:body>
                        <x-slot:footer>
                            <x-table-pagination :paginator="$consultorios" />
                        </x-slot:footer>
                    </x-table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
