<x-admin-layout>
    <div class="py-10 bg-white">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8">
            
            <div class="text-center mb-10">
                <h2 class="text-xl font-bold text-[#0061F5] tracking-wide">PANEL PRINCIPAL</h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 px-4">
                <!-- Ganancias -->
                <a href="{{ route('ganancias.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-chart-line text-3xl text-green-600"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">GANANCIAS</span>
                </a>

                <!-- Usuarios -->
                <a href="{{ route('users.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">USUARIOS</span>
                </a>

                <!-- Suscripciones -->
                <a href="{{ route('admin.suscripciones.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-file-invoice-dollar text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">SUSCRIPCIONES</span>
                </a>

                <!-- Clínica -->
                <a href="{{ route('clinicas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-hospital-alt text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">CLÍNICA</span>
                </a>

                <!-- Consultorios -->
                <a href="{{ route('consultorios.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-building text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">CONSULTORIOS</span>
                </a>

                <!-- Horarios -->
                <a href="{{ route('horarios.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-clock text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">HORARIOS</span>
                </a>

                <!-- Pacientes -->
                <a href="{{ route('pacientes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">PACIENTES</span>
                </a>

                <!-- Citas -->
                <a href="{{ route('citas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-calendar-alt text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">CITAS</span>
                </a>

                <!-- Expedientes -->
                <a href="{{ route('expedientes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-folder-open text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">EXPEDIENTES</span>
                </a>

                <!-- Plantillas -->
                <a href="{{ route('plantillas.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-file-medical-alt text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">PLANTILLAS</span>
                </a>

                <!-- Especialidades -->
                <a href="{{ route('especialidades.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-stethoscope text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">ESPECIALIDADES</span>
                </a>

                <!-- Catalogo -->
                <a href="{{ route('catalogos.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-list-ul text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">CATALOGO</span>
                </a>

                <!-- Paquetes -->
                <a href="{{ route('paquetes.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-box-open text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">PAQUETES</span>
                </a>

                <!-- Configuración -->
                <a href="{{ route('configuraciones.index') }}" class="flex flex-col items-center justify-center p-8 bg-white border border-gray-100 rounded-2xl shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-lg transition-all duration-300 group h-48">
                    <div class="w-20 h-20 bg-[#E6F0FF] rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-cog text-3xl text-[#0061F5]"></i>
                    </div>
                    <span class="font-bold text-gray-800 text-sm tracking-wide">CONFIGURACIÓN</span>
                </a>
            </div>
        </div>
    </div>
</x-admin-layout>
