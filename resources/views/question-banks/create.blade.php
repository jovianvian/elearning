@extends('layouts.app')

@section('content')
    <x-ui.page-header title="Create Question Bank" subtitle="Set subject, visibility, and title for a new bank." />

    <div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <form method="POST" action="{{ route('question-banks.store') }}">
            @include('question-banks._form', ['buttonLabel' => 'Save'])
        </form>
    </div>
@endsection
