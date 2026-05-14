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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('citas.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">{{ __('citas.title') }}</h2>
                        <a href="{{ route('citas.create') }}" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm flex items-center">
                            <i class="fas fa-plus mr-2"></i> {{ __('common.buttons.new') }}
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('citas.columns.datetime') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('citas.columns.doctor') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('citas.columns.patient') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('clinicas.columns.consultorios') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('citas.columns.status') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('citas.columns.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($citas as $cita)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $cita->fecha->format('d/m/Y') }} <br>
                                            {{ $cita->hora_inicio->format('g:i A') }} - {{ $cita->hora_fin->format('g:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $cita->doctor->name }} {{ $cita->doctor->apellido_paterno }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $cita->paciente->name }} {{ $cita->paciente->apellido_paterno }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $cita->consultorio->nombre ?? __('common.none') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($cita->estado === 'pendiente') bg-yellow-100 text-yellow-800 
                                                @elseif($cita->estado === 'confirmada') bg-green-100 text-green-800 
                                                @elseif($cita->estado === 'cancelada') bg-red-100 text-red-800 
                                                @elseif($cita->estado === 'requiere_reagenda') bg-orange-100 text-orange-800 
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ trans_enum("citas.statuses.{$cita->estado}", ucfirst($cita->estado)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end items-center space-x-2">
                                                <a href="{{ route('consulta-cobros.show', $cita) }}"
                                                   class="inline-flex items-center justify-center rounded-md transition-colors shadow-sm"
                                                   style="width: 2.5rem; height: 2.5rem; background-color: {{ $cita->cobro ? '#475569' : '#64748b' }}; color: #ffffff;"
                                                   title="{{ __('cobros.title') }}">
                                                    <i class="fas fa-cash-register text-lg" style="color: #ffffff;"></i>
                                                </a>
                                                <a href="{{ route('consultas.create', ['cita_id' => $cita->id]) }}" 
                                                   onclick="return confirm('¿{{ __('consultas.title') }}?')"
                                                   class="inline-flex items-center justify-center w-10 h-10 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" 
                                                   title="{{ __('consultas.create') }}">
                                                    <i class="fas fa-stethoscope text-lg"></i>
                                                </a>
                                                <a href="{{ route('citas.edit', $cita) }}" class="inline-flex items-center justify-center w-10 h-10 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="{{ __('common.buttons.edit') }}">
                                                    <i class="fas fa-edit text-lg"></i>
                                                </a>
                                                <form action="{{ route('citas.destroy', $cita) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('common.confirm_delete') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex cursor-pointer items-center justify-center w-10 h-10 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="{{ __('common.buttons.delete') }}">
                                                        <i class="fas fa-trash text-lg"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">{{ __('common.no_results') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $citas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
