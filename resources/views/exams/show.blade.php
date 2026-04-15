@extends('layouts.app', ['title' => 'Detail Ujian'])

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold">{{ $exam->title }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $exam->course?->title }} - {{ $exam->course?->schoolClass?->name }}</p>
                <div class="text-xs text-slate-500 mt-2">
                    {{ $exam->start_at?->format('d M Y H:i') }} s/d {{ $exam->end_at?->format('d M Y H:i') }} |
                    {{ $exam->duration_minutes }} {{ __('ui.minutes_short') }} |
                    Status: {{ $exam->effective_status }}
                </div>
            </div>
            <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                @if(auth()->user()->hasRole('super_admin','admin','teacher'))
                    <a href="{{ route('exams.edit', $exam) }}" class="px-3 py-2 border rounded-lg text-sm">{{ __('ui.edit') }}</a>
                    <a href="{{ route('exams.results', $exam) }}" class="px-3 py-2 border rounded-lg text-sm">Hasil</a>
                @endif
                <a href="{{ route('exams.index') }}" class="px-3 py-2 border rounded-lg text-sm">{{ __('ui.back') }}</a>
            </div>
        </div>

        @if($exam->description)
            <div class="mt-4 text-sm text-slate-700">{{ $exam->description }}</div>
        @endif

        <div class="mt-4 flex flex-wrap gap-2 text-xs">
            <span class="px-2 py-1 rounded bg-slate-100">Acak Soal: {{ $exam->shuffle_questions ? __('ui.yes') : __('ui.no') }}</span>
            <span class="px-2 py-1 rounded bg-slate-100">Acak Opsi: {{ $exam->shuffle_options ? __('ui.yes') : __('ui.no') }}</span>
            <span class="px-2 py-1 rounded bg-slate-100">Auto Submit: {{ $exam->auto_submit ? __('ui.yes') : __('ui.no') }}</span>
            <span class="px-2 py-1 rounded bg-slate-100">Maks Attempt: {{ $exam->max_attempts }}</span>
            <span class="px-2 py-1 rounded bg-slate-100">Target: {{ $exam->target_score ?? 100 }}</span>
            <span class="px-2 py-1 rounded bg-slate-100">Objektif: {{ $exam->objective_weight_percent ?? 60 }}%</span>
            <span class="px-2 py-1 rounded bg-slate-100">Esai: {{ $exam->essay_weight_percent ?? 40 }}%</span>
            <span class="px-2 py-1 rounded bg-slate-100">Dipublikasi: {{ $exam->is_published ? __('ui.yes') : __('ui.no') }}</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <h3 class="font-semibold mb-3">Set Soal ({{ $exam->examQuestions->count() }})</h3>
        <div class="space-y-2 text-sm">
            @foreach($exam->examQuestions->sortBy('question_order') as $examQuestion)
                <div class="border border-slate-100 rounded-lg p-3">
                    <div class="font-medium">{{ $examQuestion->question_order }}. {{ \Illuminate\Support\Str::limit($examQuestion->question?->question_text, 160) }}</div>
                    <div class="text-xs text-slate-500 mt-1">{{ $examQuestion->question?->type }} | {{ $examQuestion->points }} poin</div>
                </div>
            @endforeach
        </div>
    </div>

    @if(auth()->user()->hasRole('super_admin','admin','teacher'))
        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
            <h3 class="font-semibold mb-3">Publikasi Hasil</h3>
            <form method="POST" action="{{ route('exams.publish-results', $exam) }}" class="flex flex-col sm:flex-row gap-2" data-submit-lock="true">
                @csrf
                <input name="note" class="flex-1 rounded-lg border-slate-300" placeholder="Catatan opsional">
                <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm" data-loading-text="{{ __('ui.processing') }}">Publikasikan</button>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 mobile-table-scroll">
        <div class="px-4 py-3 border-b font-semibold">Daftar Attempt</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-3 text-left">Siswa</th>
                <th class="p-3 text-left">Mulai</th>
                <th class="p-3 text-left">Submit</th>
                <th class="p-3 text-center">Nilai</th>
                <th class="p-3 text-center">Publikasi</th>
                <th class="p-3 text-right">Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($attempts as $attempt)
                <tr class="border-t border-slate-100">
                    <td class="p-3">{{ $attempt->student?->full_name }}</td>
                    <td class="p-3 text-xs">{{ $attempt->started_at?->format('d M Y H:i') }}</td>
                    <td class="p-3 text-xs">{{ $attempt->submitted_at?->format('d M Y H:i') ?? $attempt->auto_submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                    <td class="p-3 text-center">{{ $attempt->final_score }}</td>
                    <td class="p-3 text-center">{{ $attempt->is_published ? __('ui.yes') : __('ui.no') }}</td>
                    <td class="p-3 text-right">
                        @if(auth()->user()->hasRole('super_admin','admin','teacher'))
                            <a href="{{ route('exam-grading.show', $attempt) }}" class="text-sky-600 text-xs">Nilai</a>
                        @else
                            <span class="text-slate-400 text-xs">Baca saja</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-6 text-center text-slate-500">Belum ada attempt.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $attempts->links() }}
@endsection
