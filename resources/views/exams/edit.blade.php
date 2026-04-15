@extends('layouts.app', ['title' => __('ui.edit') . ' ' . __('ui.exams')])

@section('content')
    <x-ui.page-header :title="__('ui.edit') . ' ' . __('ui.exams')" :subtitle="__('ui.edit_exam_subtitle')" />

    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 max-w-6xl">
        <form method="POST" action="{{ route('exams.update', $exam) }}">
            @method('PUT')
            @include('exams._form', ['buttonLabel' => __('ui.update_exam')])
        </form>
    </div>
@endsection
