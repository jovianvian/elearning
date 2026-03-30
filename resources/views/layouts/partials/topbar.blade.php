@php
    $pageTitle = $title ?? 'Dashboard';
    $notifCount = \App\Models\UserNotification::where('user_id', auth()->id())->where('is_read', false)->count();
    $latestNotif = \App\Models\UserNotification::with('notification')
        ->where('user_id', auth()->id())
        ->latest()
        ->limit(5)
        ->get();
@endphp

<header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
    <div class="tera-page h-16 flex items-center justify-between px-4 sm:px-6">
        <div class="flex items-center gap-3">
            <button type="button" class="lg:hidden inline-flex p-2 rounded-lg border border-slate-200" @click="sidebarOpen = true">
                <i data-lucide="menu" class="w-5 h-5"></i>
            </button>
            <div>
                <h1 class="text-base md:text-lg font-bold text-ink">{{ $pageTitle }}</h1>
                <p class="hidden md:block text-xs text-slate-500">Teramia E-Learning • SMP Teramia</p>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <div class="hidden sm:flex items-center rounded-xl border border-slate-200 bg-slate-50 px-1 py-1">
                <form method="POST" action="{{ route('locale.update', 'id') }}" class="inline">
                    @csrf
                    <button class="px-2.5 py-1 text-xs rounded-lg {{ app()->getLocale() === 'id' ? 'bg-primary text-white' : 'text-slate-600' }}">ID</button>
                </form>
                <form method="POST" action="{{ route('locale.update', 'en') }}" class="inline">
                    @csrf
                    <button class="px-2.5 py-1 text-xs rounded-lg {{ app()->getLocale() === 'en' ? 'bg-primary text-white' : 'text-slate-600' }}">EN</button>
                </form>
            </div>

            <div x-data="{ open: false }" class="relative">
                <button type="button" class="relative inline-flex p-2 rounded-xl border border-slate-200 hover:bg-slate-50" @click="open = !open">
                    <i data-lucide="bell" class="w-5 h-5 text-slate-700"></i>
                    @if($notifCount > 0)
                        <span class="absolute -top-1 -right-1 h-5 min-w-[20px] px-1 rounded-full bg-redx text-white text-[10px] font-bold grid place-items-center">{{ $notifCount }}</span>
                    @endif
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-2xl shadow-soft overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold">Notifications</h3>
                        <a href="{{ route('notifications.index') }}" class="text-xs text-primary font-semibold">View all</a>
                    </div>
                    <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                        @forelse($latestNotif as $item)
                            @php($n = $item->notification)
                            <div class="px-4 py-3 {{ $item->is_read ? 'bg-white' : 'bg-sky-50/60' }}">
                                <p class="text-sm font-semibold text-slate-800">{{ app()->getLocale() === 'en' ? ($n->title_en ?: $n->title) : $n->title }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ app()->getLocale() === 'en' ? ($n->body_en ?: $n->body) : $n->body }}</p>
                            </div>
                        @empty
                            <div class="px-4 py-8 text-center text-sm text-slate-500">No notifications</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div x-data="{ open: false }" class="relative">
                <button class="flex items-center gap-2 rounded-xl border border-slate-200 pl-2 pr-3 py-1.5 hover:bg-slate-50" @click="open = !open">
                    <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-primary to-skyx text-white grid place-items-center font-bold text-xs">
                        {{ strtoupper(substr(auth()->user()->full_name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="hidden md:block text-left">
                        <p class="text-xs font-semibold leading-none">{{ auth()->user()->full_name }}</p>
                        <p class="text-[11px] text-slate-500 leading-none mt-1">{{ auth()->user()->role->name ?? '-' }}</p>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-500"></i>
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 mt-2 w-52 bg-white border border-slate-200 rounded-xl shadow-soft p-2">
                    <a href="{{ route('notifications.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm hover:bg-slate-50">
                        <i data-lucide="bell" class="w-4 h-4"></i> Notifications
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm hover:bg-rose-50 text-rose-600">
                            <i data-lucide="log-out" class="w-4 h-4"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

