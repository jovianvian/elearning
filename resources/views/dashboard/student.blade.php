@extends('layouts.app', ['title' => __('ui.dashboard_student')])

@section('content')
    <x-ui.page-header title="{{ __('ui.dashboard_student') }}" subtitle="{{ __('ui.subtitle_student_dashboard') }}">
        <x-slot:actions>
            <a href="{{ route('student-exams.index') }}" class="tera-btn tera-btn-primary"><i data-lucide="notepad-text" class="w-4 h-4"></i>{{ __('ui.open_my_exams') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid md:grid-cols-3 gap-4">
        <x-ui.stat-card :title="__('ui.active_courses')" :value="$stats['courses']" icon="folders" color="primary" />
        <x-ui.stat-card :title="__('ui.pending_exams')" :value="$stats['pending_exams']" icon="hourglass" color="yellow" />
        <x-ui.stat-card :title="__('ui.published_results')" :value="$stats['published_results']" icon="medal" color="green" />
    </div>

    <div class="grid lg:grid-cols-[.9fr_1.1fr] gap-4">
        <div class="tera-card">
            <div class="tera-card-body">
                <h3 class="font-bold text-sm mb-3">{{ __('ui.profile_summary') }}</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('ui.name') }}</span><span class="font-semibold">{{ $stats['profile']['name'] }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('ui.username') }}</span><span class="font-semibold">{{ $stats['profile']['username'] }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">{{ __('ui.email') }}</span><span class="font-semibold">{{ $stats['profile']['email'] ?: '-' }}</span></div>
                </div>
            </div>
        </div>
        <div class="tera-card">
            <div class="tera-card-body">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-sm">{{ __('ui.latest_notifications') }}</h3>
                    <a href="{{ route('notifications.index') }}" class="text-xs text-primary font-semibold">{{ __('ui.view_all') }}</a>
                </div>
                <div class="space-y-2">
                    @forelse($stats['notifications'] as $item)
                        @php($n = $item->notification)
                        <div class="rounded-xl border px-3 py-2.5 {{ $item->is_read ? 'bg-slate-50 border-slate-200' : 'bg-sky-50 border-sky-200' }}">
                            <p class="text-sm font-semibold text-slate-800">{{ app()->getLocale() === 'en' ? ($n->title_en ?: $n->title) : $n->title }}</p>
                            <p class="text-xs text-slate-600 mt-0.5">{{ app()->getLocale() === 'en' ? ($n->body_en ?: $n->body) : $n->body }}</p>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">{{ __('ui.no_notifications_yet') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
