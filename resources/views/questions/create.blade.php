@extends('layouts.app')

@section('content')
    <x-ui.page-header :title="__('ui.add_question')" :subtitle="__('ui.create_question_for_bank', ['bank' => $questionBank->title])" />

    <div class="max-w-4xl bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <form method="POST" action="{{ route('question-banks.questions.store', $questionBank) }}" enctype="multipart/form-data">
            @include('questions._form', ['buttonLabel' => __('ui.save_question'), 'question' => null, 'questionBankId' => $questionBank->id])
        </form>
    </div>
@endsection
