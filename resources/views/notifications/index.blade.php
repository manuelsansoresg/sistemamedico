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
                            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ __('notifications.title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800">{{ __('notifications.title') }}</h2>
                            <p class="mt-1 text-sm text-gray-500">{{ __('notifications.subtitle') }}</p>
                        </div>
                        <form method="POST" action="{{ route('notifications.read_all') }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="px-4 py-2 bg-[#0061F5] text-white font-bold rounded-md hover:bg-[#0051CC] transition-colors shadow-sm">
                                <i class="fas fa-check-double mr-2"></i>{{ __('notifications.actions.mark_all_read') }}
                            </button>
                        </form>
                    </div>

                    <div class="space-y-3">
                        @forelse($notifications as $notification)
                            @php
                                $data = $notification->data ?? [];
                                $message = $data['mensaje'] ?? $data['message'] ?? __('notifications.fallback_message');
                                $title = $data['titulo'] ?? $data['title'] ?? __('notifications.item_title');
                                $actionUrl = $data['action_url'] ?? null;
                                $icon = $data['icon'] ?? 'fa-bell';
                            @endphp
                            <div class="flex flex-col gap-4 rounded-xl border p-4 sm:flex-row sm:items-start sm:justify-between" style="border-color: {{ $notification->read_at ? '#E2E8F0' : '#BFDBFE' }}; background: {{ $notification->read_at ? '#FFFFFF' : '#F8FAFC' }};">
                                <div class="flex gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full" style="background: {{ $notification->read_at ? '#E2E8F0' : '#0061F5' }}; color: {{ $notification->read_at ? '#1E293B' : '#FFFFFF' }};">
                                        <i class="fas {{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-bold text-gray-900">{{ $title }}</h3>
                                            @unless($notification->read_at)
                                                <span class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-700">{{ __('notifications.unread') }}</span>
                                            @endunless
                                        </div>
                                        <p class="mt-1 text-sm text-gray-600">{{ $message }}</p>
                                        <p class="mt-2 text-xs text-gray-400">{{ $notification->created_at?->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <div class="flex shrink-0 flex-wrap gap-2 sm:justify-end">
                                    @if($actionUrl)
                                        <a href="{{ route('notifications.open', $notification->id) }}" class="inline-flex items-center rounded-md bg-[#0061F5] px-3 py-2 text-sm font-bold text-white hover:bg-[#0051CC]">
                                            <i class="fas fa-external-link-alt mr-2"></i>{{ __('notifications.actions.open') }}
                                        </a>
                                    @endif
                                    @unless($notification->read_at)
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center rounded-md bg-gray-100 px-3 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200">
                                                <i class="fas fa-check mr-2"></i>{{ __('notifications.actions.mark_read') }}
                                            </button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" onsubmit="return confirm('{{ __('notifications.confirm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-bold text-white hover:bg-red-700">
                                            <i class="fas fa-trash mr-2"></i>{{ __('notifications.actions.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-gray-300 p-10 text-center text-gray-500">
                                <i class="fas fa-bell-slash mb-3 block text-4xl text-gray-300"></i>
                                {{ __('notifications.empty') }}
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
