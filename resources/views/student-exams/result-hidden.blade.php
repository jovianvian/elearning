@extends('layouts.app', ['title' => 'Exam Result'])

@section('content')
    <div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-xl font-semibold">Result Not Published</h2>
        <p class="text-sm text-slate-600 mt-2">
            Your attempt for <strong>{{ $attempt->exam->title }}</strong> has been submitted.
            Results are still hidden and will appear after teacher publishes them.
        </p>
        <div class="mt-4">
            <a href="{{ route('student-exams.index') }}" class="px-4 py-2 border rounded-lg text-sm">Back to My Exams</a>
        </div>
    </div>
@endsection

