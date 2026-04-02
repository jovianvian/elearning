@extends('layouts.app')

@section('content')
    <x-ui.page-header title="Add Question" subtitle="Create a new question for bank: {{ $questionBank->title }}" />

    <div class="max-w-4xl bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <form method="POST" action="{{ route('question-banks.questions.store', $questionBank) }}">
            @include('questions._form', ['buttonLabel' => 'Save Question', 'question' => null, 'questionBankId' => $questionBank->id])
        </form>
    </div>
@endsection
