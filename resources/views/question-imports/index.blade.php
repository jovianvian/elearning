@extends('layouts.app', ['title' => 'Question Imports'])

@section('content')
    <x-ui.page-header title="Question Import Center" subtitle="Import questions using supported formats and review import history.">
        <x-slot:actions>
            <a href="{{ route('question-imports.create') }}" class="tera-btn tera-btn-primary">
                <i data-lucide="file-up" class="w-4 h-4"></i>New Import
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
                <tr>
                    <th class="text-left">Date</th>
                    <th class="text-left">By</th>
                    <th class="text-left">Subject</th>
                    <th class="text-left">Type</th>
                    <th class="text-left">File</th>
                    <th class="text-left">Result</th>
                    <th class="text-left">Errors</th>
                </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr class="align-top">
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
                    <td colspan="7" class="text-center text-slate-500 py-8">No import logs yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
