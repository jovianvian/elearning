@extends('layouts.app', ['title' => 'Grading'])

@section('content')
    <x-ui.page-header title="Exam Grading" subtitle="Review submissions, score responses, and publish results." />

    <x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search exam or student">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.subjects') }}</label>
                <select name="subject_id" class="tera-select">
                    <option value="">All</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string)request('subject_id') === (string)$subject->id)>{{ $subject->name_id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.classes') }}</label>
                <select name="class_id" class="tera-select">
                    <option value="">All</option>
                    @foreach($classes as $klass)
                        <option value="{{ $klass->id }}" @selected((string)request('class_id') === (string)$klass->id)>{{ $klass->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.status') }}</label>
                <select name="status" class="tera-select">
                    <option value="">All</option>
                    @foreach(['submitted', 'auto_submitted', 'graded'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
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
                <th>Exam</th>
                <th>Student</th>
                <th>Status</th>
                <th>Objective</th>
                <th>Essay</th>
                <th>Final</th>
                <th>Action</th>
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
                    <td class="text-center">{{ $attempt->status }}</td>
                    <td class="text-center">{{ $attempt->score_objective }}</td>
                    <td class="text-center">{{ $attempt->score_essay }}</td>
                    <td class="text-center font-semibold">{{ $attempt->final_score }}</td>
                    <td>
                        <a href="{{ route('exam-grading.show', $attempt) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">Open</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-slate-500 py-8">No attempts to grade.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $attempts->links() }}</div>
@endsection
