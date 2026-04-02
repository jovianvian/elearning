@extends('layouts.app', ['title' => 'Create Exam'])

@section('content')
    <x-ui.page-header title="Create Exam" subtitle="Set schedule, options, and question composition for this exam." />

    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 max-w-6xl">
        <form method="POST" action="{{ route('exams.store') }}">
            @include('exams._form', ['buttonLabel' => 'Save Exam'])
        </form>
    </div>
@endsection
