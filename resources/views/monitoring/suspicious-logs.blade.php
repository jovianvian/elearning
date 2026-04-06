@extends('layouts.app', ['title' => __('ui.suspicious_logs')])

@section('content')
    <x-ui.page-header :title="__('ui.suspicious_activity_logs_title')" :subtitle="__('ui.suspicious_activity_logs_subtitle')" />

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_activity_student_note')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.activity_type') }}</label>
                <select name="activity_type" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($activityTypes as $activityType)
                        <option value="{{ $activityType }}" @selected(request('activity_type') === $activityType)>{{ $activityType }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.severity') }}</label>
                <select name="severity" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach(['low','medium','high'] as $severity)
                        <option value="{{ $severity }}" @selected(request('severity') === $severity)>{{ __('ui.severity_'.$severity) }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
                <tr>
                    <th>{{ __('ui.no') }}</th>
                    <th>{{ __('ui.time') }}</th>
                    <th>{{ __('ui.student') }}</th>
                    <th>{{ __('ui.exams') }}</th>
                    <th>{{ __('ui.activity') }}</th>
                    <th>{{ __('ui.severity') }}</th>
                    <th>{{ __('ui.note') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $logs->firstItem() + $loop->index }}</td>
                        <td class="text-xs">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                        <td>{{ $log->user?->full_name ?? '-' }}</td>
                        <td>{{ $log->attempt?->exam?->title ?? '-' }}</td>
                        <td>{{ $log->activity_type }}</td>
                        <td>
                            <span class="tera-badge {{ $log->severity === 'high' ? 'bg-red-100 text-red-700' : ($log->severity === 'medium' ? 'bg-yellow-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ __('ui.severity_'.$log->severity) }}
                            </span>
                        </td>
                        <td class="text-xs">{{ $log->note }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-slate-500">{{ __('ui.no_suspicious_logs') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
