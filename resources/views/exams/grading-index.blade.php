@extends('layouts.app', ['title' => __('ui.grading')])

@section('content')
<div x-data="{}" data-async-list data-fragment="#grading-table-fragment">
    <x-ui.page-header :title="__('ui.exam_grading_title')" :subtitle="__('ui.exam_grading_subtitle')" />

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_exam_or_student')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.subjects') }}</label>
                <select name="subject_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string)request('subject_id') === (string)$subject->id)>{{ $subject->name_id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.classes') }}</label>
                <select name="class_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($classes as $klass)
                        <option value="{{ $klass->id }}" @selected((string)request('class_id') === (string)$klass->id)>{{ $klass->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.status') }}</label>
                <select name="status" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach(['submitted', 'auto_submitted', 'graded'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('ui.status_'.$status) }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div id="grading-table-fragment">
        <div class="tera-table-wrap">
            <table class="tera-table">
                <thead>
                <tr>
                    <th>{{ __('ui.no') }}</th>
                    <th>{{ __('ui.exams') }}</th>
                    <th>{{ __('ui.student') }}</th>
                    <th>{{ __('ui.status') }}</th>
                    <th>{{ __('ui.objective') }}</th>
                    <th>{{ __('ui.essay') }}</th>
                    <th>{{ __('ui.final_score') }}</th>
                    <th>{{ __('ui.action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($attempts as $attempt)
                    <tr>
                        <td>{{ $attempts->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="font-medium">{{ $attempt->exam?->title }}</div>
                            <div class="text-xs text-slate-500">{{ $attempt->exam?->course?->subject?->name_id }}</div>
                        </td>
                        <td>{{ $attempt->student?->full_name }}</td>
                        <td class="text-center">{{ __('ui.status_'.$attempt->status) }}</td>
                        <td class="text-center">{{ $attempt->score_objective }}</td>
                        <td class="text-center">{{ $attempt->score_essay }}</td>
                        <td class="text-center font-semibold">{{ $attempt->final_score }}</td>
                        <td>
                            <a href="{{ route('exam-grading.show', $attempt) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">{{ __('ui.open') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-500 py-8">{{ __('ui.no_attempts_to_grade') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $attempts->links() }}</div>
    </div>
</div>
@endsection
