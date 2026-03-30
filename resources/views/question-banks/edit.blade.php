@extends('layouts.app')

@section('content')
    <div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-xl font-semibold mb-1">Edit Question Bank</h2>
        <p class="text-sm text-slate-500 mb-6">Update metadata and visibility.</p>

        <form method="POST" action="{{ route('question-banks.update', $questionBank) }}">
            @method('PUT')
            @include('question-banks._form', ['buttonLabel' => 'Update'])
        </form>
    </div>
@endsection

