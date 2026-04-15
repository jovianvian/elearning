@extends('layouts.app')

@section('content')
    <x-ui.page-header :title="__('ui.edit_question')" :subtitle="__('ui.edit_question_subtitle')" />

    <div class="max-w-4xl bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <form method="POST" action="{{ route('questions.update', $question) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('questions._form', ['buttonLabel' => __('ui.update_question'), 'questionBankId' => $question->question_bank_id])
        </form>
    </div>
@endsection
