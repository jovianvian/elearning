@extends('layouts.app', ['title' => 'My Exams'])

@section('content')
    <div>
        <h2 class="text-xl font-semibold">My Exams</h2>
        <p class="text-sm text-slate-500">Available exams from your enrolled courses.</p>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        @forelse($exams as $exam)
            @php($latestAttempt = $exam->latest_attempt)
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <div class="flex justify-between items-start gap-3">
                    <div>
                        <h3 class="font-semibold">{{ $exam->title }}</h3>
                        <p class="text-xs text-slate-500 mt-1">{{ $exam->course?->title }}</p>
                    </div>
                    <span class="px-2 py-1 rounded text-xs bg-skyx/20 text-sky-700">{{ $exam->effective_status }}</span>
                </div>

                <div class="mt-3 text-xs text-slate-500 space-y-1">
                    <div>Window: {{ $exam->start_at?->format('d M Y H:i') }} - {{ $exam->end_at?->format('d M Y H:i') }}</div>
                    <div>Duration: {{ $exam->duration_minutes }} min | Max attempt: {{ $exam->max_attempts }}</div>
                </div>

                @if($latestAttempt && $latestAttempt->status !== \App\Models\ExamAttempt::STATUS_IN_PROGRESS)
                    <div class="mt-4 flex items-center justify-between gap-2">
                        <span class="text-xs text-slate-500">Status attempt: {{ str_replace('_', ' ', $latestAttempt->status) }}</span>
                        <a href="{{ route('student-exams.attempt.result', $latestAttempt) }}" class="tera-btn tera-btn-primary !px-4 !py-2 !text-sm">
                            {{ $latestAttempt->is_published || $exam->show_result_after_submit ? 'View Result' : 'Waiting Result' }}
                        </a>
                    </div>
                @else
                    <form method="POST" action="{{ route('student-exams.start', $exam) }}" class="mt-4">
                        @csrf
                        <button class="tera-btn tera-btn-primary !px-4 !py-2 !text-sm">
                            {{ $latestAttempt ? 'Continue Attempt' : 'Start Exam' }}
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-xl border border-slate-200 p-6 text-slate-500">No exams available right now.</div>
        @endforelse
    </div>

    {{ $exams->links() }}
@endsection
