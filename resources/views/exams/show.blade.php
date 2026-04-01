@extends('layouts.app', ['title' => 'Exam Detail'])

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold">{{ $exam->title }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $exam->course?->title }} - {{ $exam->course?->schoolClass?->name }}</p>
                <div class="text-xs text-slate-500 mt-2">
                    {{ $exam->start_at?->format('d M Y H:i') }} s/d {{ $exam->end_at?->format('d M Y H:i') }} |
                    {{ $exam->duration_minutes }} min |
                    Status: {{ $exam->effective_status }}
                </div>
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                @if(auth()->user()->hasRole('super_admin','admin','teacher'))
                    <a href="{{ route('exams.edit', $exam) }}" class="px-3 py-2 border rounded-lg text-sm">Edit</a>
                    <a href="{{ route('exams.results', $exam) }}" class="px-3 py-2 border rounded-lg text-sm">Results</a>
                @endif
                <a href="{{ route('exams.index') }}" class="px-3 py-2 border rounded-lg text-sm">Back</a>
            </div>
        </div>

        @if($exam->description)
            <div class="mt-4 text-sm text-slate-700">{{ $exam->description }}</div>
        @endif

        <div class="mt-4 flex flex-wrap gap-2 text-xs">
            <span class="px-2 py-1 rounded bg-slate-100">Shuffle Q: {{ $exam->shuffle_questions ? 'Yes' : 'No' }}</span>
            <span class="px-2 py-1 rounded bg-slate-100">Shuffle Opt: {{ $exam->shuffle_options ? 'Yes' : 'No' }}</span>
            <span class="px-2 py-1 rounded bg-slate-100">Auto Submit: {{ $exam->auto_submit ? 'Yes' : 'No' }}</span>
            <span class="px-2 py-1 rounded bg-slate-100">Max Attempts: {{ $exam->max_attempts }}</span>
            <span class="px-2 py-1 rounded bg-slate-100">Published: {{ $exam->is_published ? 'Yes' : 'No' }}</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <h3 class="font-semibold mb-3">Question Set ({{ $exam->examQuestions->count() }})</h3>
        <div class="space-y-2 text-sm">
            @foreach($exam->examQuestions->sortBy('question_order') as $examQuestion)
                <div class="border border-slate-100 rounded-lg p-3">
                    <div class="font-medium">{{ $examQuestion->question_order }}. {{ \Illuminate\Support\Str::limit($examQuestion->question?->question_text, 160) }}</div>
                    <div class="text-xs text-slate-500 mt-1">{{ $examQuestion->question?->type }} | {{ $examQuestion->points }} pts</div>
                </div>
            @endforeach
        </div>
    </div>

    @if(auth()->user()->hasRole('super_admin','admin','teacher'))
        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
            <h3 class="font-semibold mb-3">Publish Results</h3>
            <form method="POST" action="{{ route('exams.publish-results', $exam) }}" class="flex flex-col sm:flex-row gap-2">
                @csrf
                <input name="note" class="flex-1 rounded-lg border-slate-300" placeholder="Optional note">
                <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm">Publish</button>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 mobile-table-scroll">
        <div class="px-4 py-3 border-b font-semibold">Attempts</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-3 text-left">Student</th>
                <th class="p-3 text-left">Started</th>
                <th class="p-3 text-left">Submitted</th>
                <th class="p-3 text-center">Score</th>
                <th class="p-3 text-center">Published</th>
                <th class="p-3 text-right">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($attempts as $attempt)
                <tr class="border-t border-slate-100">
                    <td class="p-3">{{ $attempt->student?->full_name }}</td>
                    <td class="p-3 text-xs">{{ $attempt->started_at?->format('d M Y H:i') }}</td>
                    <td class="p-3 text-xs">{{ $attempt->submitted_at?->format('d M Y H:i') ?? $attempt->auto_submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                    <td class="p-3 text-center">{{ $attempt->final_score }}</td>
                    <td class="p-3 text-center">{{ $attempt->is_published ? 'Yes' : 'No' }}</td>
                    <td class="p-3 text-right">
                        @if(auth()->user()->hasRole('super_admin','admin','teacher'))
                            <a href="{{ route('exam-grading.show', $attempt) }}" class="text-sky-600 text-xs">Grade</a>
                        @else
                            <span class="text-slate-400 text-xs">Read only</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-6 text-center text-slate-500">No attempts yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $attempts->links() }}
@endsection
