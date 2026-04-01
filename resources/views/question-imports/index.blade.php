@extends('layouts.app', ['title' => 'Question Imports'])

@section('content')
    <x-ui.page-header title="Question Import Center" subtitle="Import questions using supported formats and review import history.">
        <x-slot:actions>
            <a href="{{ route('question-imports.create') }}" class="tera-btn tera-btn-primary">
                <i data-lucide="file-up" class="w-4 h-4"></i>New Import
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search import file, subject, or user">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.subjects') }}</label>
                <select name="subject_id" class="tera-select">
                    <option value="">All</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string)request('subject_id') === (string)$subject->id)>{{ $subject->name_id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">Type</label>
                <select name="import_type" class="tera-select">
                    <option value="">All</option>
                    <option value="aiken" @selected(request('import_type') === 'aiken')>AIKEN</option>
                    <option value="csv" @selected(request('import_type') === 'csv')>CSV</option>
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Date</th>
                    <th>By</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>File</th>
                    <th>Result</th>
                    <th>Errors</th>
                </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr class="align-top">
                    <td>{{ $logs->firstItem() + $loop->index }}</td>
                    <td>{{ $log->created_at?->format('d M Y H:i') }}</td>
                    <td>{{ $log->user->full_name ?? '-' }}</td>
                    <td>{{ $log->subject->name_id ?? '-' }}</td>
                    <td class="uppercase">{{ $log->import_type }}</td>
                    <td>{{ $log->file_name }}</td>
                    <td>
                        <div class="text-emerald-700">Success: {{ $log->success_count }}</div>
                        <div class="text-rose-700">Failed: {{ $log->failed_count }}</div>
                        <div class="text-slate-500">Total: {{ $log->total_rows }}</div>
                    </td>
                    <td class="text-xs text-slate-600">
                        @php($errors = $log->error_log ?? [])
                        @if(empty($errors))
                            <span class="text-emerald-700">No errors</span>
                        @else
                            <div class="space-y-1 max-h-40 overflow-auto">
                                @foreach($errors as $error)
                                    <div>Row {{ $error['row'] ?? '-' }}: {{ $error['error'] ?? '-' }}</div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-slate-500 py-8">No import logs yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
