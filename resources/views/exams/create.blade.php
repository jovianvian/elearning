@extends('layouts.app', ['title' => 'Create Exam'])

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-6 max-w-6xl">
        <h2 class="text-xl font-semibold mb-1">Create Exam</h2>
        <p class="text-sm text-slate-500 mb-6">Set schedule, exam options, and questions.</p>

        <form method="POST" action="{{ route('exams.store') }}">
            @include('exams._form', ['buttonLabel' => 'Save Exam'])
        </form>
    </div>
@endsection

