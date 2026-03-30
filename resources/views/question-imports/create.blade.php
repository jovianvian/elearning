@extends('layouts.app')

@section('content')
    <div class="max-w-3xl bg-white rounded-xl border border-slate-200 p-6">
        <h2 class="text-xl font-semibold mb-1">Import Questions</h2>
        <p class="text-sm text-slate-500 mb-6">Supported methods: AIKEN (.txt) and CSV template.</p>

        <div class="mb-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ route('question-imports.template.aiken') }}" class="px-3 py-2 rounded border">Download AIKEN Template</a>
            <a href="{{ route('question-imports.template.csv') }}" class="px-3 py-2 rounded border">Download CSV Template</a>
        </div>

        <form method="POST" action="{{ route('question-imports.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm mb-1">Question Bank</label>
                <select name="question_bank_id" class="w-full rounded-lg border-slate-300" required>
                    <option value="">Select bank</option>
                    @foreach($banks as $bank)
                        <option value="{{ $bank->id }}" @selected(old('question_bank_id') == $bank->id)>
                            {{ $bank->title }} - {{ $bank->subject->name_id ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">Import Type</label>
                <select name="import_type" class="w-full rounded-lg border-slate-300" required>
                    <option value="aiken" @selected(old('import_type') === 'aiken')>AIKEN (Multiple Choice)</option>
                    <option value="csv" @selected(old('import_type') === 'csv')>CSV (MCQ, Short Answer, Essay)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">File</label>
                <input type="file" name="file" class="w-full rounded-lg border-slate-300" required>
            </div>

            <div class="flex gap-2">
                <button class="px-4 py-2 bg-primary text-white rounded-lg text-sm">Start Import</button>
                <a href="{{ route('question-imports.index') }}" class="px-4 py-2 rounded-lg border text-sm">Back to Logs</a>
            </div>
        </form>
    </div>
@endsection

