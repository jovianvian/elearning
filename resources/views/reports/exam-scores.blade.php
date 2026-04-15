@extends('layouts.app', ['title' => __('ui.exam_scores')])

@section('content')
<div data-async-list data-fragment="#exam-scores-fragment">
    <x-ui.page-header :title="__('ui.student_scores')" :subtitle="$exam->title" />

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_student_name_or_nis')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.status') }}</label>
                <select name="status" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach(['submitted','auto_submitted','graded'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div id="exam-scores-fragment">
        <div class="grid gap-3 sm:grid-cols-3 mb-3">
            <div class="tera-card">
                <div class="tera-card-body py-3">
                    <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('ui.total_attempts') }}</div>
                    <div class="text-xl font-bold text-slate-900">{{ number_format($attempts->total()) }}</div>
                </div>
            </div>
            <div class="tera-card">
                <div class="tera-card-body py-3">
                    <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('ui.average_score') }}</div>
                    <div class="text-xl font-bold text-slate-900">{{ number_format((float) $attempts->getCollection()->avg('final_score'), 2) }}</div>
                </div>
            </div>
            <div class="tera-card">
                <div class="tera-card-body py-3">
                    <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('ui.published') }}</div>
                    <div class="text-xl font-bold text-slate-900">{{ number_format($attempts->getCollection()->where('is_published', true)->count()) }}</div>
                </div>
            </div>
        </div>

        <div class="tera-table-wrap">
            <table class="tera-table">
                <thead>
                    <tr>
                        <th>{{ __('ui.no') }}</th>
                        <th>{{ __('ui.student') }}</th>
                        <th>{{ __('ui.status') }}</th>
                        <th>{{ __('ui.objective') }}</th>
                        <th>{{ __('ui.essay') }}</th>
                        <th>{{ __('ui.final_score') }}</th>
                        <th>{{ __('ui.published') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attempts as $attempt)
                        <tr>
                            <td>{{ $attempts->firstItem() + $loop->index }}</td>
                            <td>{{ $attempt->student?->full_name }}</td>
                            <td><span class="tera-badge tera-status-badge bg-skyx/20 text-sky-700">{{ __('ui.status_'.$attempt->status) }}</span></td>
                            <td>{{ $attempt->score_objective }}</td>
                            <td>{{ $attempt->score_essay }}</td>
                            <td class="font-semibold">{{ $attempt->final_score }}</td>
                            <td><span class="tera-badge tera-status-badge {{ $attempt->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ $attempt->is_published ? __('ui.yes') : __('ui.no') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-slate-500">{{ __('ui.no_score_data') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $attempts->links() }}</div>
    </div>
</div>
@endsection
