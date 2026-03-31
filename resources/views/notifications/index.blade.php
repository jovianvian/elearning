@extends('layouts.app', ['title' => 'Notifications'])

@section('content')
<div x-data="notificationPage()">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold">Notifications</h2>
            <p class="text-sm text-slate-500">System and exam notifications for your account.</p>
        </div>
        <button type="button" class="tera-btn tera-btn-muted" @click="markAllRead" :disabled="loadingAll">
            <span x-show="!loadingAll">Mark All Read</span>
            <span x-show="loadingAll" x-cloak>Processing...</span>
        </button>
    </div>

    <div class="space-y-3">
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
                        <button type="button" class="tera-btn tera-btn-primary !px-3 !py-1.5 !text-xs" @click="markRead({{ $item->id }})">Mark Read</button>
                    @else
                        <span class="text-xs text-emerald-700 bg-emerald-100 px-2 py-1 rounded">Read</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white border rounded-xl p-6 text-slate-500">No notifications yet.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>

<script>
function notificationPage() {
    return {
        loadingAll: false,

        async markRead(id) {
            try {
                const res = await fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || 'Failed to mark notification.');
                window.location.reload();
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Error', text: error.message });
            }
        },

        async markAllRead() {
            this.loadingAll = true;
            try {
                const res = await fetch(`/notifications/read-all`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || 'Failed to mark all notifications.');
                window.location.reload();
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Error', text: error.message });
            } finally {
                this.loadingAll = false;
            }
        },
    };
}
</script>
@endsection
