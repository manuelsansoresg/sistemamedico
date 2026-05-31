<x-dynamic-component :component="$layout">
    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <nav class="flex" aria-label="Breadcrumb">
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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('pacientes.qr.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-[#0061F5]">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ __('pacientes.qr.title') }}</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $paciente->name }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                        </p>
                    </div>
                    @if($activePermissionCount > 0)
                        <a href="{{ route('public.expediente.show', $paciente->patient_public_token) }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 bg-[#0061F5] text-white text-sm font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                            <i class="fas fa-external-link-alt mr-2"></i>{{ __('pacientes.qr.open_public_record') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
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

            <div class="space-y-6">
                <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <i class="fas fa-user-shield mr-2 text-[#0061F5]"></i>{{ __('pacientes.qr.permissions.title') }}
                    </h3>

                    <form action="{{ route('paciente.shared-permissions.store') }}" method="POST" class="space-y-4" x-data="doctorPermissionSearch('{{ route('paciente.qr.doctors.search') }}')">
                        @csrf

                        <div>
                            <label for="permission_type" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('pacientes.qr.permissions.type') }}</label>
                            <select id="permission_type" name="permission_type" class="w-full rounded-md border-gray-300 focus:border-[#0061F5] focus:ring-[#0061F5]">
                                <option value="read" @selected(old('permission_type') === 'read')>{{ __('pacientes.qr.permissions.types.read') }}</option>
                                <option value="download" @selected(old('permission_type') === 'download')>{{ __('pacientes.qr.permissions.types.download') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('permission_type')" class="mt-2" />
                        </div>

                        <div>
                            <label for="duration_hours" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('pacientes.qr.permissions.duration') }}</label>
                            <input id="duration_hours" type="number" min="5" name="duration_hours" value="{{ old('duration_hours', 5) }}" class="w-full rounded-md border-gray-300 focus:border-[#0061F5] focus:ring-[#0061F5]">
                            <p class="mt-1 text-xs text-gray-500">{{ __('pacientes.qr.permissions.duration_hint') }}</p>
                            <x-input-error :messages="$errors->get('duration_hours')" class="mt-2" />
                        </div>

                        <div class="relative">
                            <label for="doctor_search" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('pacientes.qr.permissions.doctor') }}</label>
                            <input type="hidden" name="doctor_id" x-model="selectedDoctorId">
                            <input id="doctor_search" type="text" name="doctor_search" x-model="query" @input.debounce.350ms="search" @focus="open = results.length > 0" @keydown.escape="open = false" value="{{ old('doctor_search') }}" class="w-full rounded-md border-gray-300 focus:border-[#0061F5] focus:ring-[#0061F5]" placeholder="{{ __('pacientes.qr.permissions.doctor_search_placeholder') }}" autocomplete="off">
                            <p class="mt-1 text-xs text-gray-500">{{ __('pacientes.qr.permissions.doctor_search_hint') }}</p>
                            <div x-cloak x-show="open" @click.outside="open = false" class="absolute z-20 mt-1 max-h-64 w-full overflow-y-auto rounded-md border border-gray-200 bg-white shadow-lg">
                                <template x-for="doctor in results" :key="doctor.id">
                                    <button type="button" class="flex w-full flex-col px-3 py-2 text-left hover:bg-blue-50" @click="selectDoctor(doctor)">
                                        <span class="text-sm font-semibold text-gray-800" x-text="doctor.label"></span>
                                        <span class="text-xs text-gray-500" x-text="doctor.email"></span>
                                    </button>
                                </template>
                                <div x-show="!loading && query.length >= 3 && results.length === 0" class="px-3 py-2 text-sm text-gray-500">
                                    {{ __('pacientes.qr.permissions.no_doctor_results') }}
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('doctor_id')" class="mt-2" />
                        </div>

                        <div>
                            <label for="especialidad_id" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('pacientes.qr.permissions.specialty') }}</label>
                            <select id="especialidad_id" name="especialidad_id" class="w-full rounded-md border-gray-300 focus:border-[#0061F5] focus:ring-[#0061F5]">
                                <option value="">{{ __('common.all') }}</option>
                                @foreach($especialidades as $especialidad)
                                    <option value="{{ $especialidad->id }}" @selected(old('especialidad_id') == $especialidad->id)>{{ $especialidad->nombre }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('especialidad_id')" class="mt-2" />
                        </div>

                        <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-white p-3 text-sm text-gray-700">
                            <input type="checkbox" name="can_edit_owned_records" value="1" class="mt-1 rounded border-gray-300 text-[#0061F5] focus:ring-[#0061F5]">
                            <span>{{ __('pacientes.qr.permissions.can_edit_owned_records') }}</span>
                        </label>

                        <div>
                            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-1">{{ __('pacientes.qr.permissions.notes') }}</label>
                            <textarea id="notes" name="notes" rows="2" class="w-full rounded-md border-gray-300 focus:border-[#0061F5] focus:ring-[#0061F5]">{{ old('notes') }}</textarea>
                        </div>

                        <label class="flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                            <input type="checkbox" name="accept_terms" value="1" class="mt-1 rounded border-gray-300 text-[#0061F5] focus:ring-[#0061F5]">
                            <span>{{ __('pacientes.qr.permissions.patient_terms') }}</span>
                        </label>
                        <x-input-error :messages="$errors->get('accept_terms')" class="mt-2" />

                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('paciente.qr.show') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-bold rounded-md hover:bg-gray-200 transition-colors shadow-sm">
                                {{ __('common.buttons.cancel') }}
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[#0061F5] text-white text-sm font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                <i class="fas fa-save mr-2"></i>{{ __('common.buttons.save') }}
                            </button>
                        </div>
                    </form>
                </section>

                <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <i class="fas fa-tasks mr-2 text-[#0061F5]"></i>{{ __('pacientes.qr.permissions.active_title') }}
                    </h3>

                    <div class="space-y-3">
                        @forelse($permissions as $permission)
                            <div class="rounded-lg border border-gray-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="font-bold text-gray-800">
                                            {{ $permission->doctor?->name ?? $permission->external_doctor_name ?? __('pacientes.qr.permissions.no_doctor') }}
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500">
                                            {{ $permission->especialidad?->nombre ?? __('common.all') }} ·
                                            {{ $permission->allowsDownload() ? __('pacientes.qr.permissions.types.download') : __('pacientes.qr.permissions.types.read') }}
                                            @if($permission->can_edit_owned_records)
                                                · {{ __('pacientes.qr.permissions.edit_owned_badge') }}
                                            @endif
                                        </div>
                                        <div class="mt-1 text-xs text-gray-500">
                                            {{ __('pacientes.qr.permissions.expires_at', ['date' => $permission->expires_at->format('d/m/Y H:i')]) }}
                                        </div>
                                        @if($permission->temporary_access_code)
                                            <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-bold tracking-wide text-amber-900">
                                                {{ __('pacientes.qr.permissions.temporary_code') }}: {{ $permission->temporary_access_code }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-bold {{ $permission->isActive() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $permission->isActive() ? __('pacientes.qr.permissions.status.active') : __('pacientes.qr.permissions.status.inactive') }}
                                    </span>
                                </div>

                                @if($permission->isActive())
                                    <form action="{{ route('paciente.shared-permissions.revoke', $permission) }}" method="POST" class="mt-3" onsubmit="return confirm('{{ __('pacientes.qr.permissions.revoke_confirm') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-xs font-bold rounded-md hover:bg-red-700 transition-colors">
                                            <i class="fas fa-ban mr-2"></i>{{ __('pacientes.qr.permissions.revoke') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('pacientes.qr.permissions.empty') }}</p>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        function doctorPermissionSearch(searchUrl) {
            return {
                query: @json(old('doctor_search', '')),
                selectedDoctorId: @json(old('doctor_id', '')),
                results: [],
                loading: false,
                open: false,
                async search() {
                    this.selectedDoctorId = '';

                    if (this.query.length < 3) {
                        this.results = [];
                        this.open = false;
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch(`${searchUrl}?q=${encodeURIComponent(this.query)}`, {
                            headers: { Accept: 'application/json' },
                        });
                        this.results = response.ok ? await response.json() : [];
                        this.open = true;
                    } finally {
                        this.loading = false;
                    }
                },
                selectDoctor(doctor) {
                    this.selectedDoctorId = doctor.id;
                    this.query = `${doctor.label} - ${doctor.email}`;
                    this.open = false;
                },
            };
        }
    </script>
</x-dynamic-component>
