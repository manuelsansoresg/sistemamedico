<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('payment_error'))
                <div class="bg-red-50 border-l-4 border-red-500 p-4 shadow-sm rounded-md mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-800 font-semibold">{{ session('payment_error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(isset($requiresCedulaValidation) && $requiresCedulaValidation)
                @if($cedulaStatus === 'pendiente')
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-hourglass-half text-yellow-400 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-800 font-semibold">{{ __('common.professional_id') }} {{ __('status.pending') }}.</p>
                                </div>
                            </div>
                        </div>
                    @elseif($cedulaStatus === 'rechazada')
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 shadow-sm rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-times-circle text-red-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-800 font-semibold">{{ __('common.professional_id') }} {{ __('status.rejected') }}.</p>
                                </div>
                            </div>
                        </div>
                    @elseif($cedulaStatus !== 'validada')
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 shadow-sm rounded-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-id-card text-red-500 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-800 font-semibold">{{ __('common.professional_id') }} {{ __('common.required') }}.</p>
                                </div>
                            </div>
                        </div>
                    @endif
            @endif

            <!-- Subscription Alerts -->
            @if(isset($notifications) && $notifications->count() > 0)
                <div class="space-y-4">
                @foreach($notifications as $notification)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 shadow-sm rounded-md flex justify-between items-center">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700 font-bold">
                                    {{ __('common.warning') }}: {{ $notification->data['mensaje'] }}
                                </p>
                            </div>
                        </div>
                        <div>
                             <a href="{{ route('admin.notifications.read', $notification->id) }}" class="text-xs text-yellow-600 hover:text-yellow-800 underline">{{ __('common.buttons.mark_read') }}</a>
                        </div>
                    </div>
                @endforeach
                </div>
            @endif

            <!-- Section: Días Sin Citas Alert -->
            @if(isset($diasBloqueadosHoy) && $diasBloqueadosHoy->count() > 0)
                <div class="bg-red-50 border-l-4 border-red-500 p-4 shadow-sm rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-800">{{ __('dias_sin_citas') }} {{ __('status.active') }}</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($diasBloqueadosHoy as $dia)
                                        <li>
                                            <strong>{{ $dia->motivo }}</strong>
                                            @if($dia->todo_el_dia)
                                                ({{ __('dias_sin_citas.labels.all_day') }})
                                            @else
                                                ({{ \Carbon\Carbon::parse($dia->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($dia->hora_fin)->format('H:i') }})
                                            @endif
                                             - {{ __('dias_sin_citas.labels.affects') }} 
                                            @foreach($dia->consultorios as $consultorio)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                    {{ $consultorio->nombre }}
                                                </span>
                                            @endforeach
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Section 1: Citas del Día -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <i class="fas fa-calendar-day text-blue-500 mr-2"></i> {{ __('citas.today_title') }}
                        </h3>
                        <a href="{{ route('citas.index') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                            {{ __('common.buttons.view_more') }} <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    @if($citasHoy->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.time') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.patient') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.office') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.status') }}</th>
                                        <th class="px-4 py-2 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($citasHoy as $cita)
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $cita->hora_inicio->format('g:i A') }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">
                                                {{ $cita->paciente->name }} {{ $cita->paciente->apellido_paterno }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">
                                                {{ $cita->consultorio->nombre }}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    @if($cita->estado === 'pendiente') bg-yellow-100 text-yellow-800 
                                                    @elseif($cita->estado === 'confirmada') bg-green-100 text-green-800 
                                                    @elseif($cita->estado === 'cancelada') bg-red-100 text-red-800 
                                                    @elseif($cita->estado === 'requiere_reagenda') bg-orange-100 text-orange-800 
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ trans_enum("citas.statuses.{$cita->estado}", ucfirst($cita->estado)) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end items-center space-x-2">
                                                    <a href="{{ route('consulta-cobros.show', $cita) }}"
                                                       class="inline-flex items-center justify-center rounded-md transition-colors shadow-sm"
                                                       style="width: 2.5rem; height: 2.5rem; background-color: {{ $cita->cobro ? '#475569' : '#64748b' }}; color: #ffffff;"
                                                       title="{{ __('cobros.title') }}">
                                                        <i class="fas fa-cash-register text-lg" style="color: #ffffff;"></i>
                                                    </a>
                                                    <a href="{{ route('consultas.create', ['cita_id' => $cita->id]) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white font-bold rounded-md hover:bg-blue-700 transition-colors shadow-sm" title="{{ __('consultas.create') }}">
                                                        <i class="fas fa-stethoscope mr-2"></i> {{ __('common.buttons.start') }}
                                                    </a>
                                                    <a href="{{ route('citas.edit', $cita) }}" class="inline-flex items-center justify-center w-10 h-10 bg-[#0061F5] text-white rounded-md hover:bg-[#0051CC] transition-colors shadow-sm" title="{{ __('common.buttons.edit') }}">
                                                        <i class="fas fa-edit text-lg"></i>
                                                    </a>
                                                    @if($cita->estado !== 'cancelada')
                                                    <form action="{{ route('citas.destroy', $cita) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('citas.confirm.delete') }}');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex cursor-pointer items-center justify-center w-10 h-10 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors shadow-sm" title="{{ __('citas.actions.delete') }}">
                                                            <i class="fas fa-trash-alt text-xl"></i>
                                                        </button>
                                                    </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 italic">{{ __('common.no_results') }}</p>
                    @endif
                </div>
            </div>

            <!-- Section 2: Pendientes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center">
                            <i class="fas fa-tasks text-orange-500 mr-2"></i> {{ __('pendientes.dashboard_title') }}
                        </h3>
                        <a href="{{ route('pendientes.index') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                            {{ __('common.buttons.view_more') }} <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    @if($pendientes->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($pendientes as $pendiente)
                                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100 shadow-sm relative hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-xs font-bold text-gray-500 bg-white px-2 py-1 rounded border">
                                            {{ $pendiente->fecha->format('d/m/Y') }} - {{ $pendiente->hora->format('g:i A') }}
                                        </span>
                                        <a href="{{ route('pendientes.edit', $pendiente) }}" class="text-gray-400 hover:text-blue-500">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    </div>
                                    <p class="text-gray-800 text-sm font-medium line-clamp-3">
                                        {{ $pendiente->recordatorio }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 italic">{{ __('common.no_results') }}</p>
                        <a href="{{ route('pendientes.create') }}" class="mt-2 inline-flex items-center text-sm text-blue-600 hover:underline">
                            <i class="fas fa-plus mr-1"></i> {{ __('common.buttons.create_new') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 px-4">
                
                @role('doctor')
                <a href="{{ route('doctor.wizard.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-magic text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('doctor.wizard') }}</span>
                </a>

                <a href="{{ route('compras.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-shopping-cart text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.subscriptions') }}</span>
                </a>
                @endrole

                @hasanyrole('doctor|asistente')
                <a href="{{ route('users.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.users') }}</span>
                </a>

                <a href="{{ route('clinicas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-hospital-alt text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.clinics') }}</span>
                </a>

                <a href="{{ route('consultorios.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-building text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.offices') }}</span>
                </a>
                @endhasanyrole

                @role('doctor')
                <a href="{{ route('branding.edit') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user-circle text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.my_profile') }}</span>
                </a>

                <a href="{{ route('servicios.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-hand-holding-medical text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.services') }}</span>
                </a>
                @endrole

                <a href="{{ route('horarios.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-clock text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.schedules') }}</span>
                </a>

                @role('doctor')
                <a href="{{ route('ganancias.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-chart-line text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.earnings') }}</span>
                </a>
                @endrole

                <a href="{{ route('dias-sin-citas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 aspect-square shrink-0 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-calendar-times text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dias_sin_citas.title') }}</span>
                </a>

                <a href="{{ route('recursos.agenda') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <img src="{{ asset('icons/clinic-schedule.svg') }}" alt="" class="h-8 w-8" aria-hidden="true">
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.resources') }}</span>
                </a>

                <a href="{{ route('pacientes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.patients') }}</span>
                </a>

                @if(isset($canSharePacientes) && $canSharePacientes)
                <a href="{{ route('pacientes.shared.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user-friends text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.shared_profiles') }}</span>
                </a>
                @endif

                <a href="{{ route('citas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-calendar-alt text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.appointments') }}</span>
                </a>

                <a href="{{ route('pendientes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-clipboard-list text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('pendientes.card_label') }}</span>
                </a>

                <a href="{{ route('expedientes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-folder-open text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.records') }}</span>
                </a>

                @if(auth()->check() && auth()->user()->hasRole('doctor'))
                <a href="{{ route('plantillas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-file-medical-alt text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.templates') }}</span>
                </a>
                @endif

            </div>

        </div>
    </div>
</x-app-layout>
