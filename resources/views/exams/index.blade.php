@extends('layouts.app', ['title' => 'Exams'])

@section('content')
    <x-ui.page-header title="Exam Management" subtitle="Create, schedule, and manage online exams across courses.">
        <x-slot:actions>
            @if(auth()->user()->hasRole('super_admin','admin','teacher'))
                <a href="{{ route('exams.create') }}" class="tera-btn tera-btn-primary">Create Exam</a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
            <tr>
                <th class="text-left">Title</th>
                <th class="text-left">Course</th>
                <th class="text-left">Window</th>
                <th class="text-center">Attempts</th>
                <th class="text-center">Status</th>
                <th class="text-center">Published</th>
                <th class="text-right">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($exams as $exam)
                <tr>
                    <td>
                        <div class="font-medium">{{ $exam->title }}</div>
                        <div class="text-xs text-slate-500">{{ $exam->exam_type }} | {{ $exam->duration_minutes }} min</div>
                    </td>
                    <td>
                        <div>{{ $exam->course?->title }}</div>
                        <div class="text-xs text-slate-500">{{ $exam->course?->subject?->name_id }} - {{ $exam->course?->schoolClass?->name }}</div>
                    </td>
                    <td class="text-xs">
                        <div>{{ $exam->start_at?->format('d M Y H:i') }}</div>
                        <div>{{ $exam->end_at?->format('d M Y H:i') }}</div>
                    </td>
                    <td class="text-center">{{ $exam->max_attempts }}</td>
                    <td class="text-center">
                        <span class="tera-badge bg-skyx/20 text-sky-700">{{ $exam->effective_status }}</span>
                    </td>
                    <td class="text-center">{{ $exam->is_published ? 'Yes' : 'No' }}</td>
                    <td class="text-right">
                        <div class="inline-flex items-center gap-2">
                            <a href="{{ route('exams.show', $exam) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">Detail</a>
                            @if(auth()->user()->hasRole('super_admin','admin','teacher'))
                                <a href="{{ route('exams.edit', $exam) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">Edit</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-slate-500 py-8">No exams available.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $exams->links() }}</div>
@endsection
