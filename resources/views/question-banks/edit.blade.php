@extends('layouts.app')

@section('content')
    <x-ui.page-header title="Edit Question Bank" subtitle="Update question bank metadata and visibility." />

    <div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <form method="POST" action="{{ route('question-banks.update', $questionBank) }}">
            @method('PUT')
            @include('question-banks._form', ['buttonLabel' => 'Update'])
        </form>
    </div>
@endsection
