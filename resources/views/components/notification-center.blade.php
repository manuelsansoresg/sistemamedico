@auth
    @php
        $notificationUser = Auth::user();
        $unreadNotificationsCount = $notificationUser->unreadNotifications()->count();
        $recentNotifications = $notificationUser->notifications()->latest()->take(8)->get();
    @endphp

    <div x-data="{ notificationsOpen: false }" class="relative">
        <button type="button"
                @click="notificationsOpen = ! notificationsOpen"
                @keydown.escape.window="notificationsOpen = false"
                class="relative inline-flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full bg-white/15 text-white hover:bg-white/25 focus:outline-none focus:ring-2 focus:ring-white/70 transition-colors"
                aria-label="{{ __('notifications.title') }}">
            <i class="fas fa-bell text-base sm:text-lg"></i>
            @if($unreadNotificationsCount > 0)
                <span class="absolute -right-1 -top-1 min-w-5 rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white ring-2 ring-[#27ADFA]">
                    {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                </span>
            @endif
        </button>

        <div x-cloak
             x-show="notificationsOpen"
             @click.outside="notificationsOpen = false"
             x-transition.origin.top.right
             class="absolute right-0 z-50 mt-3 overflow-hidden bg-white"
             style="width: 28rem; max-width: calc(100vw - 2rem); border: 1px solid #dbeafe; border-radius: 1rem; box-shadow: 0 24px 60px rgba(30, 41, 59, 0.18);">
            <div class="text-white" style="background: #0061F5; border-top: 4px solid #27ADFA; padding: 1rem 1.25rem;">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="font-bold uppercase tracking-wide" style="font-size: 0.85rem; letter-spacing: 0.08em;">{{ __('notifications.title') }}</p>
                        <p style="font-size: 0.78rem; color: rgba(255,255,255,0.78); margin-top: 0.15rem;">{{ trans_choice('notifications.unread_count', $unreadNotificationsCount, ['count' => $unreadNotificationsCount]) }}</p>
                    </div>
                    @if($unreadNotificationsCount > 0)
                        <form method="POST" action="{{ route('notifications.read_all') }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="rounded-full text-xs font-semibold text-white" style="background: rgba(255,255,255,0.18); padding: 0.45rem 0.8rem;">
                                {{ __('notifications.actions.mark_all_read') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="overflow-y-auto divide-y divide-slate-100" style="max-height: 28rem;">
                @forelse($recentNotifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $message = $data['mensaje'] ?? $data['message'] ?? __('notifications.fallback_message');
                        $title = $data['titulo'] ?? $data['title'] ?? __('notifications.item_title');
                        $actionUrl = $data['action_url'] ?? null;
                        $icon = $data['icon'] ?? 'fa-bell';
                    @endphp
                    <div class="group flex gap-3" style="padding: 1rem 1.25rem; background: {{ $notification->read_at ? '#ffffff' : '#F8FAFC' }};">
                        <div class="mt-0.5 flex shrink-0 items-center justify-center rounded-full" style="width: 2.5rem; height: 2.5rem; background: {{ $notification->read_at ? '#E2E8F0' : '#0061F5' }}; color: {{ $notification->read_at ? '#1E293B' : '#ffffff' }};">
                            <i class="fas {{ $icon }} text-sm"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold" style="font-size: 0.95rem; line-height: 1.35; color: #1E293B;">{{ $title }}</p>
                            <p class="mt-1" style="font-size: 0.9rem; line-height: 1.5; color: #475569; white-space: normal; overflow-wrap: anywhere;">{{ $message }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-3 text-xs">
                                <span class="text-slate-400">{{ $notification->created_at?->diffForHumans() }}</span>
                                @if($actionUrl)
                                    <a href="{{ route('notifications.open', $notification->id) }}" class="font-semibold hover:underline" style="color: #0061F5;">
                                        {{ __('notifications.actions.open') }}
                                    </a>
                                @endif
                                @unless($notification->read_at)
                                    <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="font-semibold text-slate-500 hover:text-slate-800">
                                            {{ __('notifications.actions.mark_read') }}
                                        </button>
                                    </form>
                                @endunless
                                <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="inline" onsubmit="return confirm('{{ __('notifications.confirm_delete') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-semibold text-red-600 hover:text-red-700">
                                        {{ __('notifications.actions.delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center" style="padding: 2.25rem 1.5rem; color: #1E293B; background: #ffffff;">
                        <i class="fas fa-bell-slash mb-3 block text-3xl" style="color: #94A3B8;"></i>
                        <p style="font-size: 0.95rem; line-height: 1.5; color: #1E293B;">{{ __('notifications.empty') }}</p>
                    </div>
                @endforelse
            </div>

            <a href="{{ route('notifications.index') }}" class="block text-center text-sm font-bold hover:bg-slate-100" style="padding: 0.95rem 1rem; color: #0061F5; background: #F8FAFC;">
                {{ __('notifications.actions.view_all') }}
            </a>
        </div>
    </div>
@endauth
