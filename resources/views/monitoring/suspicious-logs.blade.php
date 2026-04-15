@extends('layouts.app', ['title' => __('ui.suspicious_logs')])

@section('content')
<style>
    @media (min-width: 1280px){
        .suspicious-page .tera-toolbar-fields{
            grid-template-columns:minmax(240px,.9fr) minmax(0,3.1fr);
        }
        .suspicious-page .tera-toolbar-filters{
            grid-template-columns:repeat(4,minmax(0,1fr));
            align-items:end;
        }
        .suspicious-page .suspicious-checkbox-wrap{
            min-width:0;
        }
    }
</style>
<div class="suspicious-page" data-async-list data-fragment="#suspicious-logs-fragment">
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
            <div>
                <label class="tera-label">{{ __('ui.exams') }}</label>
                <select name="exam_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}" @selected((string) request('exam_id') === (string) $exam->id)>{{ $exam->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.student') }}</label>
                <select name="student_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" @selected((string) request('student_id') === (string) $student->id)>{{ $student->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.start_date') }}</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="tera-input">
            </div>
            <div>
                <label class="tera-label">{{ __('ui.end_date') }}</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="tera-input">
            </div>
            <div>
                <label class="tera-label">{{ __('ui.min_events') }}</label>
                <input type="number" name="min_events" value="{{ request('min_events') }}" min="1" class="tera-input" placeholder="1">
            </div>
            <div class="flex items-end suspicious-checkbox-wrap">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="multi_tab_only" value="1" class="rounded border-slate-300" @checked(request()->boolean('multi_tab_only'))>
                    <span>{{ __('ui.multi_tab_only') }}</span>
                </label>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div id="suspicious-logs-fragment">
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
                        <th>{{ __('ui.count') }}</th>
                        <th>{{ __('ui.note') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $loop->index }}</td>
                            <td class="text-xs">{{ ($log->last_detected_at ?? $log->created_at)?->format('d M Y H:i:s') }}</td>
                            <td>{{ $log->user?->full_name ?? '-' }}</td>
                            <td>{{ $log->attempt?->exam?->title ?? '-' }}</td>
                            <td>{{ $log->activity_type }}</td>
                            <td>
                                <span class="tera-badge {{ $log->severity === 'high' ? 'bg-red-100 text-red-700' : ($log->severity === 'medium' ? 'bg-yellow-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                                    {{ __('ui.severity_'.$log->severity) }}
                                </span>
                            </td>
                            <td>{{ $log->event_count ?? 1 }}</td>
                            <td class="text-xs">
                                <div>{{ $log->note }}</div>
                                @php($tabId = data_get($log->context_json, 'tab_id'))
                                @if(!empty($tabId))
                                    <div class="mt-1 text-[11px] text-slate-400">tab: {{ $tabId }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-6 text-center text-slate-500">{{ __('ui.no_suspicious_logs') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
