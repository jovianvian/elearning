@extends('layouts.app')

@section('content')
    <x-ui.page-header :title="__('ui.update_question_bank')" :subtitle="__('ui.edit_question_bank_subtitle')" />

    <div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <form method="POST" action="{{ route('question-banks.update', $questionBank) }}">
            @method('PUT')
            @include('question-banks._form', ['buttonLabel' => __('ui.update')])
        </form>
    </div>
@endsection
