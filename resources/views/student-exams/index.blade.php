@extends('layouts.app', ['title' => 'My Exams'])

@section('content')
    <div>
        <h2 class="text-xl font-semibold">My Exams</h2>
        <p class="text-sm text-slate-500">Available exams from your enrolled courses.</p>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        @forelse($exams as $exam)
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

                <form method="POST" action="{{ route('student-exams.start', $exam) }}" class="mt-4">
                    @csrf
                    <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm">Start / Continue</button>
                </form>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-slate-200 p-6 text-slate-500">No exams available right now.</div>
        @endforelse
    </div>

    {{ $exams->links() }}
@endsection

