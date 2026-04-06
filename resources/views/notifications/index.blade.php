@extends('layouts.app', ['title' => __('ui.notifications')])

@section('content')
<div x-data="notificationPage()" data-async-list data-fragment="#notifications-list-fragment">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold">{{ __('ui.notifications') }}</h2>
            <p class="text-sm text-slate-500">{{ __('ui.notifications_subtitle') }}</p>
        </div>
        <button type="button" class="tera-btn tera-btn-muted" @click="markAllRead" :disabled="loadingAll">
            <span x-show="!loadingAll">{{ __('ui.mark_all_read') }}</span>
            <span x-show="loadingAll" x-cloak>{{ __('ui.processing') }}</span>
        </button>
    </div>

    <div id="notifications-list-fragment" class="space-y-3">
        @forelse($notifications as $item)
            @php($n = $item->notification)
            <div id="notif-{{ $item->id }}" class="bg-white border rounded-xl p-4 {{ $item->is_read ? '' : 'border-sky-300 bg-sky-50/40' }}">
                <div class="flex justify-between items-start gap-3">
                    <div>
                        <div class="font-semibold">{{ app()->getLocale() === 'en' ? ($n->title_en ?: $n->title) : $n->title }}</div>
                        <div class="text-sm text-slate-600 mt-1">{{ app()->getLocale() === 'en' ? ($n->body_en ?: $n->body) : $n->body }}</div>
                        <div class="text-xs text-slate-500 mt-2">{{ $item->created_at?->format('d M Y H:i') }}</div>
                    </div>
                    @if(!$item->is_read)
                        <button type="button" class="tera-btn tera-btn-primary !px-3 !py-1.5 !text-xs" @click="markRead({{ $item->id }})">{{ __('ui.mark_read') }}</button>
                    @else
                        <span class="text-xs text-emerald-700 bg-emerald-100 px-2 py-1 rounded">{{ __('ui.read') }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white border rounded-xl p-6 text-slate-500">{{ __('ui.no_notifications_yet') }}</div>
        @endforelse

        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>
</div>

<script>
function notificationPage() {
    return {
        loadingAll: false,

        async markRead(id) {
            try {
                const { response, payload } = await window.Teramia.fetchJson(`/notifications/${id}/read`, {
                    method: 'POST',
                });
                if (!response.ok) throw new Error(payload.message || @js(__('ui.failed_mark_notification')));
                await window.Teramia.toast('success', payload.message || @js(__('ui.notification_marked')));
                await window.Teramia.refreshFragment(window.location.href, '#notifications-list-fragment');
                window.Teramia.setUnreadCount(payload.unread_count ?? null);
            } catch (error) {
                window.Teramia.toast('error', error.message);
            }
        },

        async markAllRead() {
            this.loadingAll = true;
            try {
                const { response, payload } = await window.Teramia.fetchJson(`/notifications/read-all`, {
                    method: 'POST',
                });
                if (!response.ok) throw new Error(payload.message || @js(__('ui.failed_mark_all_notifications')));
                await window.Teramia.toast('success', payload.message || @js(__('ui.all_notifications_marked')));
                await window.Teramia.refreshFragment(window.location.href, '#notifications-list-fragment');
                window.Teramia.setUnreadCount(payload.unread_count ?? 0);
            } catch (error) {
                window.Teramia.toast('error', error.message);
            } finally {
                this.loadingAll = false;
            }
        },
    };
}
</script>
@endsection
