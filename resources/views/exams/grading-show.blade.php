@extends('layouts.app', ['title' => 'Penilaian Attempt'])

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-xl font-semibold">Penilaian Attempt</h2>
        <p class="text-sm text-slate-500 mt-1">
            {{ $attempt->exam?->title }} | Siswa: {{ $attempt->student?->full_name }}
        </p>
        <div class="text-xs text-slate-500 mt-1">
            Objektif: {{ $attempt->score_objective }} | Esai: {{ $attempt->score_essay }} | Nilai Akhir: {{ $attempt->final_score }}
        </div>
    </div>

    <form method="POST" action="{{ route('exam-grading.grade', $attempt) }}" class="space-y-4" data-submit-lock="true">
        @csrf
        @foreach($attempt->answers as $answer)
            @php($question = $answer->question)
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <div class="font-medium">{{ $loop->iteration }}. {{ $question?->question_text }}</div>
                @if(!empty($question?->image_url))
                    <div class="mt-2">
                        <img src="{{ $question->image_url }}" alt="Gambar soal" class="max-h-56 rounded border border-slate-200 bg-white object-contain">
                    </div>
                @endif
                <div class="text-xs text-slate-500 mt-1">Tipe: {{ $question?->type }} | Maks: {{ $question?->points }}</div>

                @if(in_array($question?->type, ['essay', 'short_answer'], true))
                    <div class="mt-3 text-sm">
                        <div class="font-medium mb-1">Jawaban Siswa</div>
                        <div class="p-3 rounded border bg-slate-50">{{ $answer->answer_text ?: '-' }}</div>
                    </div>
                    @if($question?->type === 'short_answer')
                        <div class="mt-2 text-xs text-slate-500">
                            Kunci jawaban: {{ $question?->short_answer_key ?: '-' }}
                        </div>
                    @endif

                    <div class="mt-3 grid md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm mb-1">Nilai</label>
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
                            <span class="text-slate-500">Pilihan:</span> {{ $answer->selectedOption?->option_key ?? '-' }}
                        @elseif($question?->type === 'multiple_response')
                            @php($selectedKeys = $question?->options?->whereIn('id', $answer->selected_option_ids_json ?? [])->pluck('option_key')->values()->all() ?? [])
                            <span class="text-slate-500">Pilihan:</span> {{ empty($selectedKeys) ? '-' : implode(', ', $selectedKeys) }}
                        @else
                            <span class="text-slate-500">Jawaban:</span> {{ $answer->answer_text ?: '-' }}
                        @endif
                        <span class="ml-2 px-2 py-1 text-xs rounded {{ $answer->is_correct ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $answer->is_correct ? 'Benar' : 'Salah' }}
                        </span>
                        <span class="ml-2">Nilai: {{ $answer->score }}</span>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="flex gap-2">
            <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm" data-loading-text="{{ __('ui.processing') }}">Simpan Penilaian</button>
            <a href="{{ route('exam-grading.index') }}" class="px-4 py-2 border rounded-lg text-sm">{{ __('ui.back') }}</a>
        </div>
    </form>
@endsection
