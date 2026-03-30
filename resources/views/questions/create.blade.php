@extends('layouts.app')

@section('content')
    <div class="max-w-4xl bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-xl font-semibold mb-1">Add Question</h2>
        <p class="text-sm text-slate-500 mb-6">Bank: {{ $questionBank->title }}</p>

        <form method="POST" action="{{ route('question-banks.questions.store', $questionBank) }}">
            @include('questions._form', ['buttonLabel' => 'Save Question', 'question' => null, 'questionBankId' => $questionBank->id])
        </form>
    </div>
@endsection

