@extends('layouts.app', ['title' => __('ui.exam_result')])

@section('content')
    <div class="max-w-2xl bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-xl font-semibold">{{ __('ui.result_not_published') }}</h2>
        <p class="text-sm text-slate-600 mt-2">
            {!! __('ui.result_not_published_message', ['exam' => e($attempt->exam->title)]) !!}
        </p>
        <div class="mt-4">
            <a href="{{ route('student-exams.index') }}" class="px-4 py-2 border rounded-lg text-sm">{{ __('ui.back_to_my_exams') }}</a>
        </div>
    </div>
@endsection
