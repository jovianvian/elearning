@extends('layouts.app', ['title' => 'Exam Scores'])

@section('content')
    <x-ui.page-header title="Student Scores" :subtitle="$exam->title" />

    <x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search student name or NIS">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.status') }}</label>
                <select name="status" class="tera-select">
                    <option value="">All</option>
                    @foreach(['submitted','auto_submitted','graded'] as $status)
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
                    <th>Student</th>
                    <th>Status</th>
                    <th>Objective</th>
                    <th>Essay</th>
                    <th>Final</th>
                    <th>Published</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts as $attempt)
                    <tr>
                        <td>{{ $attempts->firstItem() + $loop->index }}</td>
                        <td>{{ $attempt->student?->full_name }}</td>
                        <td>{{ $attempt->status }}</td>
                        <td>{{ $attempt->score_objective }}</td>
                        <td>{{ $attempt->score_essay }}</td>
                        <td class="font-semibold">{{ $attempt->final_score }}</td>
                        <td>{{ $attempt->is_published ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-slate-500">No score data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $attempts->links() }}</div>
@endsection
