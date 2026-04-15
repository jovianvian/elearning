@extends('layouts.app', ['title' => __('ui.create_exam')])

@section('content')
    <x-ui.page-header :title="__('ui.create_exam')" :subtitle="__('ui.create_exam_subtitle')" />

    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 max-w-6xl">
        <form method="POST" action="{{ route('exams.store') }}">
            @include('exams._form', ['buttonLabel' => __('ui.save_exam')])
        </form>
    </div>
@endsection
