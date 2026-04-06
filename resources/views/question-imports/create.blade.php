@extends('layouts.app')

@section('content')
    <x-ui.page-header title="Import Questions" subtitle="Import question bank items from AIKEN (.txt), CSV, or Excel (.xlsx)." />

    <div class="max-w-4xl space-y-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('question-imports.template.aiken') }}" class="tera-btn tera-btn-muted">Download AIKEN Template</a>
                <a href="{{ route('question-imports.template.csv') }}" class="tera-btn tera-btn-muted">Download CSV Template</a>
            </div>
        </div>

        <form method="POST" action="{{ route('question-imports.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 space-y-5">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="tera-label">Question Bank</label>
                    <select name="question_bank_id" class="tera-select" required>
                    <option value="">Select bank</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}" @selected(old('question_bank_id') == $bank->id)>
                            {{ $bank->title }} - {{ $bank->subject->name_id ?? '-' }}
                        </option>
                    @endforeach
                    </select>
                </div>

                <div>
                    <label class="tera-label">Import Type</label>
                    <select name="import_type" class="tera-select" required>
                        <option value="aiken" @selected(old('import_type') === 'aiken')>AIKEN (Multiple Choice)</option>
                        <option value="csv" @selected(old('import_type') === 'csv')>CSV / Excel (MCQ, Short Answer, Essay)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="tera-label">File</label>
                <input type="file" name="file" class="tera-input" accept=".txt,.csv,.xlsx" required>
                <p class="mt-1 text-xs text-slate-500">Maksimal 10MB. AIKEN pakai .txt, sedangkan format tabel bisa .csv atau .xlsx.</p>
            </div>

            <div class="flex flex-wrap gap-2 pt-1">
                <button class="tera-btn tera-btn-primary">Start Import</button>
                <a href="{{ route('question-imports.index') }}" class="tera-btn tera-btn-muted">Back to Logs</a>
            </div>
        </form>
    </div>
@endsection
