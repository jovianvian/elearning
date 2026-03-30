@extends('layouts.app', ['title' => 'Edit Exam'])

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-6 max-w-6xl">
        <h2 class="text-xl font-semibold mb-1">Edit Exam</h2>
        <p class="text-sm text-slate-500 mb-6">Update schedule and question set.</p>

        <form method="POST" action="{{ route('exams.update', $exam) }}">
            @method('PUT')
            @include('exams._form', ['buttonLabel' => 'Update Exam'])
        </form>
    </div>
@endsection

