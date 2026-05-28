<x-dynamic-component :component="$layout">
    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-[#0061F5]">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ __('pacientes.qr.title') }}</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $paciente->name }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                        </p>
                    </div>
                    <a href="{{ route('public.expediente.show', $paciente->patient_public_token) }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-[#0061F5] text-white text-sm font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                        <i class="fas fa-external-link-alt mr-2"></i>{{ __('pacientes.qr.open_public_record') }}
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                    <div class="inline-flex rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <img src="{{ $qrDataUri }}" alt="{{ __('pacientes.qr.title') }}" class="h-72 w-72">
                    </div>
                    <p class="mt-4 text-sm text-gray-600">
                        {{ auth()->user()->hasRole('paciente') ? __('pacientes.qr.patient_help') : __('pacientes.qr.doctor_help') }}
                    </p>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-5">
                    <div class="w-full rounded-2xl border border-[#F5C994] bg-[#FBF4EA] px-6 py-6 text-[#4B2B05] shadow-none">
                        <div class="flex items-start gap-4 sm:items-center">
                            <div class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center text-[#8A5A05] sm:mt-0">
                                <i class="fas fa-info-circle text-[24px] leading-none"></i>
                            </div>

                            <p class="m-0 text-sm font-normal leading-5 text-[#4B2B05] sm:text-base sm:leading-6">
                                {{ __('pacientes.qr.warning') }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('pacientes.qr.public_link') }}</label>
                        <input type="text" readonly value="{{ $publicUrl }}" class="w-full rounded-md border-gray-300 bg-gray-50 text-sm text-gray-700 focus:border-[#0061F5] focus:ring-[#0061F5]" onclick="this.select()">
                    </div>

                    @if(auth()->user()->hasRole('paciente') && auth()->id() === $paciente->id)
                        <form action="{{ route('paciente.qr.regenerate') }}" method="POST" onsubmit="return confirm('{{ __('pacientes.qr.regenerate_confirm') }}');">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-bold rounded-md hover:bg-red-700 transition-colors shadow-sm">
                                <i class="fas fa-sync-alt mr-2"></i>{{ __('pacientes.qr.regenerate') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
