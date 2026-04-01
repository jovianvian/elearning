@extends('layouts.app', ['title' => 'Suspicious Logs'])

@section('content')
    <x-ui.page-header title="Suspicious Activity Logs" subtitle="Monitor flagged exam behavior, session anomalies, and related activity." />

    <x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search activity, student, or note">
        <x-slot:filters>
            <div>
                <label class="tera-label">Activity Type</label>
                <select name="activity_type" class="tera-select">
                    <option value="">All</option>
                    @foreach($activityTypes as $activityType)
                        <option value="{{ $activityType }}" @selected(request('activity_type') === $activityType)>{{ $activityType }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">Severity</label>
                <select name="severity" class="tera-select">
                    <option value="">All</option>
                    @foreach(['low','medium','high'] as $severity)
                        <option value="{{ $severity }}" @selected(request('severity') === $severity)>{{ ucfirst($severity) }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Time</th>
                    <th>Student</th>
                    <th>Exam</th>
                    <th>Activity</th>
                    <th>Severity</th>
                    <th>Note</th>
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
                                {{ $log->severity }}
                            </span>
                        </td>
                        <td class="text-xs">{{ $log->note }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-slate-500">No suspicious activity logs.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
