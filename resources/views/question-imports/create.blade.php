@extends('layouts.app')

@section('content')
    <x-ui.page-header :title="__('ui.import_questions_title')" :subtitle="__('ui.import_questions_subtitle')" />

    <div class="max-w-4xl space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('question-imports.template.aiken') }}" class="tera-btn tera-btn-muted">{{ __('ui.download_aiken_template') }}</a>
                <a href="{{ route('question-imports.template.csv') }}" class="tera-btn tera-btn-muted">{{ __('ui.download_csv_template') }}</a>
            </div>
        </div>

        <form method="POST" action="{{ route('question-imports.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 space-y-5">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="tera-label">{{ __('ui.question_bank') }}</label>
                    <select name="question_bank_id" class="tera-select" required>
                    <option value="">{{ __('ui.select_bank') }}</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}" @selected(old('question_bank_id') == $bank->id)>
                            {{ $bank->title }} - {{ $bank->subject->name_id ?? '-' }}
                        </option>
                    @endforeach
                    </select>
                </div>

                <div>
                    <label class="tera-label">{{ __('ui.import_type') }}</label>
                    <select name="import_type" class="tera-select" required>
                        <option value="aiken" @selected(old('import_type') === 'aiken')>{{ __('ui.import_type_aiken_full') }}</option>
                        <option value="csv" @selected(old('import_type') === 'csv')>{{ __('ui.import_type_csv_full') }}</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="tera-label">{{ __('ui.file') }}</label>
                <input type="file" name="file" class="tera-input" accept=".txt,.csv,.xlsx" required>
                <p class="mt-1 text-xs text-slate-500">{{ __('ui.import_file_helper') }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ __('ui.import_image_helper') }}</p>
            </div>

            <div class="flex flex-wrap gap-2 pt-1">
                <button class="tera-btn tera-btn-primary">{{ __('ui.start_import') }}</button>
                <a href="{{ route('question-imports.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.back_to_logs') }}</a>
            </div>
        </form>
    </div>
@endsection
