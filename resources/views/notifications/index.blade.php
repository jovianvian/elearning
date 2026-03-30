@extends('layouts.app', ['title' => 'Notifications'])

@section('content')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold">Notifications</h2>
            <p class="text-sm text-slate-500">System and exam notifications for your account.</p>
        </div>
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="px-4 py-2 border rounded-lg text-sm">Mark All Read</button>
        </form>
    </div>

    <div class="space-y-3">
        @forelse($notifications as $item)
            @php($n = $item->notification)
            <div class="bg-white border rounded-xl p-4 {{ $item->is_read ? '' : 'border-sky-300 bg-sky-50/40' }}">
                <div class="flex justify-between items-start gap-3">
                    <div>
                        <div class="font-semibold">{{ app()->getLocale() === 'en' ? ($n->title_en ?: $n->title) : $n->title }}</div>
                        <div class="text-sm text-slate-600 mt-1">{{ app()->getLocale() === 'en' ? ($n->body_en ?: $n->body) : $n->body }}</div>
                        <div class="text-xs text-slate-500 mt-2">{{ $item->created_at?->format('d M Y H:i') }}</div>
                    </div>
                    @if(!$item->is_read)
                        <form method="POST" action="{{ route('notifications.read', $item) }}">
                            @csrf
                            <button class="px-3 py-1.5 bg-primary text-white rounded text-xs">Mark Read</button>
                        </form>
                    @else
                        <span class="text-xs text-emerald-700 bg-emerald-100 px-2 py-1 rounded">Read</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white border rounded-xl p-6 text-slate-500">No notifications yet.</div>
        @endforelse
    </div>

    {{ $notifications->links() }}
@endsection

