@extends('layouts.app', ['title' => 'Edit Exam'])

@section('content')
    <x-ui.page-header title="Edit Exam" subtitle="Update schedule, options, and selected questions." />

    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 max-w-6xl">
        <form method="POST" action="{{ route('exams.update', $exam) }}">
            @method('PUT')
            @include('exams._form', ['buttonLabel' => 'Update Exam'])
        </form>
    </div>
@endsection
