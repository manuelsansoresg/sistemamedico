<x-admin-layout>
    <div class="py-10">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#0061F5]">
                            <i class="fas fa-home mr-2"></i>
                            {{ __('common.breadcrumbs.home') }}
                        </a>
                    </li>
                    <li class="inline-flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                        <a href="{{ route('expedientes.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-[#0061F5] md:ml-2">{{ __('expedientes.title') }}</a>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('ia.summary.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-[#0061F5]">
                <div class="p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-[#0061F5]">
                                <i class="fas fa-magic"></i>
                                {{ __('ia.summary.badge') }}
                            </div>
                            <h2 class="mt-4 text-2xl font-bold text-gray-900">{{ __('ia.summary.title') }}</h2>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $paciente->name }} {{ $paciente->apellido_paterno }} {{ $paciente->apellido_materno }}
                            </p>
                        </div>
                        <a href="{{ route('expedientes.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition-colors hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>{{ __('common.buttons.back') }}
                        </a>
                    </div>
                </div>
            </section>

            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-5">
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $status === 'cached' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $status === 'cached' ? __('ia.summary.cached') : __('ia.summary.generated') }}
                        </span>
                        @if($generatedAt)
                            <span>{{ __('ia.summary.generated_at', ['date' => $generatedAt->format('d/m/Y H:i')]) }}</span>
                        @endif
                    </div>

                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-5">
                        <div class="prose max-w-none text-sm leading-6 text-gray-700 whitespace-pre-line">{{ $summary }}</div>
                    </div>

                    <p class="text-xs text-gray-500">{{ __('ia.summary.disclaimer') }}</p>
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
