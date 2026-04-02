@extends('layouts.app')

@section('content')
    <x-ui.page-header title="Edit Question" subtitle="Update question content and scoring settings." />

    <div class="max-w-4xl bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <form method="POST" action="{{ route('questions.update', $question) }}">
            @method('PUT')
            @include('questions._form', ['buttonLabel' => 'Update Question', 'questionBankId' => $question->question_bank_id])
        </form>
    </div>
@endsection
