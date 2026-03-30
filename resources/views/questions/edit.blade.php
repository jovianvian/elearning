@extends('layouts.app')

@section('content')
    <div class="max-w-4xl bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-xl font-semibold mb-1">Edit Question</h2>
        <p class="text-sm text-slate-500 mb-6">Bank: {{ $question->bank->title ?? '-' }}</p>

        <form method="POST" action="{{ route('questions.update', $question) }}">
            @method('PUT')
            @include('questions._form', ['buttonLabel' => 'Update Question', 'questionBankId' => $question->question_bank_id])
        </form>
    </div>
@endsection

