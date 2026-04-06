@extends('layouts.app', ['title' => __('ui.exam_result')])

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-xl font-semibold">{{ __('ui.exam_result') }}</h2>
        <p class="text-sm text-slate-500 mt-1">{{ $attempt->exam->title }}</p>

        <div class="grid md:grid-cols-3 gap-3 mt-4 text-sm">
            <div class="p-3 rounded border bg-slate-50">
                <div class="text-slate-500">{{ __('ui.objective') }}</div>
                <div class="text-lg font-semibold">{{ $attempt->score_objective }}</div>
            </div>
            <div class="p-3 rounded border bg-slate-50">
                <div class="text-slate-500">{{ __('ui.essay') }}</div>
                <div class="text-lg font-semibold">{{ $attempt->score_essay }}</div>
            </div>
            <div class="p-3 rounded border bg-slate-50">
                <div class="text-slate-500">{{ __('ui.final_score') }}</div>
                <div class="text-lg font-semibold">{{ $attempt->final_score }}</div>
            </div>
        </div>
    </div>

    <div class="space-y-3">
        @foreach($attempt->answers as $answer)
            @php($question = $answer->question)
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <div class="font-medium">{{ $loop->iteration }}. {{ $question?->question_text }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ ucfirst($question?->type) }}</div>

                <div class="mt-2 text-sm">
                    @if($question?->type === 'multiple_choice')
                        <div>{{ __('ui.your_answer') }}: {{ $answer->selectedOption?->option_key ?? '-' }}</div>
                    @else
                        <div>{{ __('ui.your_answer') }}: {{ $answer->answer_text ?: '-' }}</div>
                    @endif

                    @if($question?->type !== 'essay')
                        <div class="mt-1">
                            <span class="px-2 py-1 rounded text-xs {{ $answer->is_correct ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $answer->is_correct ? __('ui.correct') : __('ui.wrong') }}
                            </span>
                        </div>
                    @endif

                    <div class="mt-1">{{ __('ui.score') }}: {{ $answer->score }}</div>
                    @if($answer->teacher_feedback)
                        <div class="mt-1 text-slate-600">{{ __('ui.feedback') }}: {{ $answer->teacher_feedback }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <a href="{{ route('student-exams.index') }}" class="inline-block px-4 py-2 border rounded-lg text-sm">{{ __('ui.back_to_my_exams') }}</a>
@endsection
