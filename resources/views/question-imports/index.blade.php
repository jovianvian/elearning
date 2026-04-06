@extends('layouts.app', ['title' => __('ui.question_imports')])

@section('content')
<div x-data="{}" data-async-list data-fragment="#question-import-logs-fragment">
    <x-ui.page-header :title="__('ui.question_import_center')" :subtitle="__('ui.question_import_center_subtitle')">
        <x-slot:actions>
            <a href="{{ route('question-imports.create') }}" class="tera-btn tera-btn-primary">
                <i data-lucide="file-up" class="w-4 h-4"></i>{{ __('ui.new_import') }}
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_import_file_subject_user')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.subjects') }}</label>
                <select name="subject_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string)request('subject_id') === (string)$subject->id)>{{ $subject->name_id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.type') }}</label>
                <select name="import_type" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    <option value="aiken" @selected(request('import_type') === 'aiken')>AIKEN</option>
                    <option value="csv" @selected(request('import_type') === 'csv')>CSV</option>
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div id="question-import-logs-fragment">
        <div class="tera-table-wrap">
            <table class="tera-table">
                <thead>
                    <tr>
                        <th>{{ __('ui.no') }}</th>
                        <th>{{ __('ui.date') }}</th>
                        <th>{{ __('ui.by') }}</th>
                        <th>{{ __('ui.subjects') }}</th>
                        <th>{{ __('ui.type') }}</th>
                        <th>{{ __('ui.file') }}</th>
                        <th>{{ __('ui.result') }}</th>
                        <th>{{ __('ui.errors') }}</th>
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
                            <div class="text-emerald-700">{{ __('ui.success') }}: {{ $log->success_count }}</div>
                            <div class="text-rose-700">{{ __('ui.failed') }}: {{ $log->failed_count }}</div>
                            <div class="text-slate-500">{{ __('ui.total') }}: {{ $log->total_rows }}</div>
                        </td>
                        <td class="text-xs text-slate-600">
                            @php($errors = $log->error_log ?? [])
                            @if(empty($errors))
                                <span class="text-emerald-700">{{ __('ui.no_errors') }}</span>
                            @else
                                <div class="space-y-1 max-h-40 overflow-auto">
                                    @foreach($errors as $error)
                                        <div>{{ __('ui.row') }} {{ $error['row'] ?? '-' }}: {{ $error['error'] ?? '-' }}</div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-500 py-8">{{ __('ui.no_import_logs_yet') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
