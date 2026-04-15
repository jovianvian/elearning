@extends('layouts.app', ['title' => __('ui.reports')])

@section('content')
    <x-ui.page-header :title="__('ui.reports_analytics_title')" :subtitle="__('ui.reports_analytics_subtitle')" />

    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="tera-card">
            <div class="tera-card-body">
                <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('ui.total_attempts') }}</div>
                <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($totalAttempts) }}</div>
            </div>
        </div>
        <div class="tera-card">
            <div class="tera-card-body">
                <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('ui.average_score') }}</div>
                <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($averageFinalScore, 2) }}</div>
            </div>
        </div>
        <div class="tera-card">
            <div class="tera-card-body">
                <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('ui.severity_high') }}</div>
                <div class="mt-1 text-2xl font-extrabold text-red-700">{{ number_format($highSeverityCount) }}</div>
            </div>
        </div>
        <div class="tera-card">
            <div class="tera-card-body">
                <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('ui.today_logins') }}</div>
                <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($todayLoginCount) }}</div>
            </div>
        </div>
    </div>

    <div class="tera-card">
        <div class="tera-card-body">
        <h3 class="font-semibold mb-3">{{ __('ui.exam_recap_by_status') }}</h3>
        <div class="grid gap-2 text-sm sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            @foreach(['draft','scheduled','active','closed','graded','archived'] as $status)
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <div class="text-slate-500">{{ __('ui.status_'.$status) }}</div>
                    <div class="text-lg font-semibold">{{ $examRecap[$status] ?? 0 }}</div>
                </div>
            @endforeach
        </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="tera-card">
            <div class="tera-card-body">
            <h3 class="font-semibold mb-3">{{ __('ui.exam_results_per_class') }}</h3>
            <div class="space-y-2 text-sm">
                @forelse($classResults as $row)
                    <div class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2">
                        <span>{{ $row->class_name }} ({{ $row->attempts }} {{ __('ui.attempts') }})</span>
                        <span class="font-semibold">{{ number_format((float)$row->avg_score, 2) }}</span>
                    </div>
                @empty
                    <div class="text-slate-500">{{ __('ui.no_data') }}</div>
                @endforelse
            </div>
            </div>
        </div>

        <div class="tera-card">
            <div class="tera-card-body">
            <h3 class="font-semibold mb-3">{{ __('ui.exam_results_per_subject') }}</h3>
            <div class="space-y-2 text-sm">
                @forelse($subjectResults as $row)
                    <div class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2">
                        <span>{{ $row->subject_name }} ({{ $row->attempts }} {{ __('ui.attempts') }})</span>
                        <span class="font-semibold">{{ number_format((float)$row->avg_score, 2) }}</span>
                    </div>
                @empty
                    <div class="text-slate-500">{{ __('ui.no_data') }}</div>
                @endforelse
            </div>
            </div>
        </div>
    </div>

    <div class="tera-card">
        <div class="tera-card-body">
        <h3 class="font-semibold mb-3">{{ __('ui.student_score_list_per_exam') }}</h3>
        <div class="flex flex-wrap gap-2 text-sm">
            @forelse($examList as $exam)
                <a href="{{ route('reports.exam-scores', $exam) }}" class="tera-btn tera-btn-muted !px-3 !py-2">{{ $exam->title }}</a>
            @empty
                <span class="text-slate-500">{{ __('ui.no_exams_yet') }}</span>
            @endforelse
        </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="tera-card">
            <div class="tera-card-body">
            <h3 class="font-semibold mb-3">{{ __('ui.login_activity_summary_last_7_days') }}</h3>
            <div class="space-y-2 text-sm">
                @forelse($loginSummary as $row)
                    <div class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2">
                        <span>{{ \Illuminate\Support\Carbon::parse($row->login_date)->format('d M Y') }}</span>
                        <span class="font-semibold">{{ $row->total }}</span>
                    </div>
                @empty
                    <div class="text-slate-500">{{ __('ui.no_login_data') }}</div>
                @endforelse
            </div>
            </div>
        </div>

        <div class="tera-card">
            <div class="tera-card-body">
            <h3 class="font-semibold mb-3">{{ __('ui.suspicious_activity_summary') }}</h3>
            <div class="space-y-2 text-sm">
                @forelse($suspiciousSummary as $row)
                    <div class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2">
                        <span>{{ $row->activity_type }}</span>
                        <span class="font-semibold">{{ $row->total }}</span>
                    </div>
                @empty
                    <div class="text-slate-500">{{ __('ui.no_suspicious_logs') }}</div>
                @endforelse
            </div>
            </div>
        </div>
    </div>
@endsection
