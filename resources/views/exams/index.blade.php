@extends('layouts.app', ['title' => 'Exams'])

@section('content')
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold">Exams</h2>
            <p class="text-sm text-slate-500">Exam schedule and management.</p>
        </div>
        @if(auth()->user()->hasRole('super_admin','admin','teacher'))
            <a href="{{ route('exams.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm">Create Exam</a>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-3 text-left">Title</th>
                <th class="p-3 text-left">Course</th>
                <th class="p-3 text-left">Window</th>
                <th class="p-3 text-center">Attempts</th>
                <th class="p-3 text-center">Status</th>
                <th class="p-3 text-center">Published</th>
                <th class="p-3 text-right">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($exams as $exam)
                <tr class="border-t border-slate-100">
                    <td class="p-3">
                        <div class="font-medium">{{ $exam->title }}</div>
                        <div class="text-xs text-slate-500">{{ $exam->exam_type }} | {{ $exam->duration_minutes }} min</div>
                    </td>
                    <td class="p-3">
                        <div>{{ $exam->course?->title }}</div>
                        <div class="text-xs text-slate-500">{{ $exam->course?->subject?->name_id }} - {{ $exam->course?->schoolClass?->name }}</div>
                    </td>
                    <td class="p-3 text-xs">
                        <div>{{ $exam->start_at?->format('d M Y H:i') }}</div>
                        <div>{{ $exam->end_at?->format('d M Y H:i') }}</div>
                    </td>
                    <td class="p-3 text-center">{{ $exam->max_attempts }}</td>
                    <td class="p-3 text-center">
                        <span class="px-2 py-1 rounded text-xs bg-skyx/20 text-sky-700">{{ $exam->effective_status }}</span>
                    </td>
                    <td class="p-3 text-center">{{ $exam->is_published ? 'Yes' : 'No' }}</td>
                    <td class="p-3">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('exams.show', $exam) }}" class="px-3 py-1.5 rounded border text-xs">Detail</a>
                            @if(auth()->user()->hasRole('super_admin','admin','teacher'))
                                <a href="{{ route('exams.edit', $exam) }}" class="px-3 py-1.5 rounded border text-xs">Edit</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-6 text-center text-slate-500">No exams available.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $exams->links() }}
@endsection

