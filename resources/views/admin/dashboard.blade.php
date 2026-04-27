<x-admin-layout>
    <div class="py-10 bg-white">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
            
            <div class="text-center mb-10">
                <h2 class="text-xl font-bold text-[#0061F5] tracking-wide">{{ __('dashboard.title') }}</h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 px-4">
                <a href="{{ route('ganancias.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-chart-line text-3xl text-green-600"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.earnings') }}</span>
                </a>

                <a href="{{ route('users.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.users') }}</span>
                </a>

                <a href="{{ route('admin.suscripciones.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-file-invoice-dollar text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.subscriptions') }}</span>
                </a>

                <a href="{{ route('clinicas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-hospital-alt text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.clinics') }}</span>
                </a>

                <a href="{{ route('recursos.agenda') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-door-open text-3xl text-[#2563EB]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.resources') }}</span>
                </a>

                <a href="{{ route('consultorios.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-building text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.offices') }}</span>
                </a>

                <a href="{{ route('branding.edit') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-palette text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.branding') }}</span>
                </a>

                <a href="{{ route('horarios.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-clock text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.schedules') }}</span>
                </a>

                <a href="{{ route('pacientes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.patients') }}</span>
                </a>

                <a href="{{ route('pacientes.shared.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user-friends text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.shared_profiles') }}</span>
                </a>

                <a href="{{ route('citas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-calendar-alt text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.appointments') }}</span>
                </a>

                <a href="{{ route('expedientes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-folder-open text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.records') }}</span>
                </a>

                <a href="{{ route('plantillas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-file-medical-alt text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.templates') }}</span>
                </a>

                <a href="{{ route('especialidades.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-stethoscope text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.specialties') }}</span>
                </a>

                <a href="{{ route('catalogos.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-list-ul text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.catalog') }}</span>
                </a>

                <a href="{{ route('paquetes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-box-open text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.packages') }}</span>
                </a>

                <a href="{{ route('configuraciones.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-cog text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="dashboard-card-label">{{ __('dashboard.card_labels.configuration') }}</span>
                </a>

                @role('root')
                    <a href="{{ route('admin.audit.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-3xl shadow-sm hover:shadow-md transition-all duration-300 group h-48">
                        <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-shield-alt text-3xl text-[#0061F5]"></i>
                        </div>
                        <span class="dashboard-card-label">{{ __('dashboard.card_labels.audit') }}</span>
                    </a>
                @endrole
            </div>
        </div>
    </div>
</x-admin-layout>
