@extends('layouts.app', ['title' => 'Grading'])

@section('content')
    <x-ui.page-header title="Exam Grading" subtitle="Review submissions, score responses, and publish results." />

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
            <tr>
                <th class="text-left">Exam</th>
                <th class="text-left">Student</th>
                <th class="text-center">Status</th>
                <th class="text-center">Objective</th>
                <th class="text-center">Essay</th>
                <th class="text-center">Final</th>
                <th class="text-right">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($attempts as $attempt)
                <tr>
                    <td>
                        <div class="font-medium">{{ $attempt->exam?->title }}</div>
                        <div class="text-xs text-slate-500">{{ $attempt->exam?->course?->subject?->name_id }}</div>
                    </td>
                    <td>{{ $attempt->student?->full_name }}</td>
                    <td class="text-center">{{ $attempt->status }}</td>
                    <td class="text-center">{{ $attempt->score_objective }}</td>
                    <td class="text-center">{{ $attempt->score_essay }}</td>
                    <td class="text-center font-semibold">{{ $attempt->final_score }}</td>
                    <td class="text-right">
                        <a href="{{ route('exam-grading.show', $attempt) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">Open</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-500 py-8">No attempts to grade.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $attempts->links() }}</div>
@endsection
