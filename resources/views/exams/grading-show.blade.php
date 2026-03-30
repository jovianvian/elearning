@extends('layouts.app', ['title' => 'Grade Attempt'])

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-xl font-semibold">Grade Attempt</h2>
        <p class="text-sm text-slate-500 mt-1">
            {{ $attempt->exam?->title }} | Student: {{ $attempt->student?->full_name }}
        </p>
        <div class="text-xs text-slate-500 mt-1">
            Objective: {{ $attempt->score_objective }} | Essay: {{ $attempt->score_essay }} | Final: {{ $attempt->final_score }}
        </div>
    </div>

    <form method="POST" action="{{ route('exam-grading.grade', $attempt) }}" class="space-y-4">
        @csrf
        @foreach($attempt->answers as $answer)
            @php($question = $answer->question)
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <div class="font-medium">{{ $loop->iteration }}. {{ $question?->question_text }}</div>
                <div class="text-xs text-slate-500 mt-1">Type: {{ $question?->type }} | Max: {{ $question?->points }}</div>

                @if($question?->type === 'essay')
                    <div class="mt-3 text-sm">
                        <div class="font-medium mb-1">Student Answer</div>
                        <div class="p-3 rounded border bg-slate-50">{{ $answer->answer_text ?: '-' }}</div>
                    </div>

                    <div class="mt-3 grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm mb-1">Score</label>
                            <input type="number" step="0.1" min="0" max="{{ $question?->points }}" name="grades[{{ $answer->id }}][score]" value="{{ old("grades.{$answer->id}.score", $answer->score) }}" class="w-full rounded-lg border-slate-300">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Feedback</label>
                            <input name="grades[{{ $answer->id }}][teacher_feedback]" value="{{ old("grades.{$answer->id}.teacher_feedback", $answer->teacher_feedback) }}" class="w-full rounded-lg border-slate-300">
                        </div>
                    </div>
                @else
                    <div class="mt-2 text-sm">
                        @if($question?->type === 'multiple_choice')
                            <span class="text-slate-500">Selected:</span> {{ $answer->selectedOption?->option_key ?? '-' }}
                        @else
                            <span class="text-slate-500">Answer:</span> {{ $answer->answer_text ?: '-' }}
                        @endif
                        <span class="ml-2 px-2 py-1 text-xs rounded {{ $answer->is_correct ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $answer->is_correct ? 'Correct' : 'Wrong' }}
                        </span>
                        <span class="ml-2">Score: {{ $answer->score }}</span>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="flex gap-2">
            <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm">Save Grading</button>
            <a href="{{ route('exam-grading.index') }}" class="px-4 py-2 border rounded-lg text-sm">Back</a>
        </div>
    </form>
@endsection

