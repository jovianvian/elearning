@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold">Question Import Logs</h2>
            <p class="text-sm text-slate-500">Track AIKEN/CSV import history and failed rows.</p>
        </div>
        <a href="{{ route('question-imports.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm">New Import</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">By</th>
                <th class="px-4 py-3 text-left">Subject</th>
                <th class="px-4 py-3 text-left">Type</th>
                <th class="px-4 py-3 text-left">File</th>
                <th class="px-4 py-3 text-left">Result</th>
                <th class="px-4 py-3 text-left">Errors</th>
            </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr class="border-t border-slate-100 align-top">
                    <td class="px-4 py-3">{{ $log->created_at?->format('d M Y H:i') }}</td>
                    <td class="px-4 py-3">{{ $log->user->full_name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ $log->subject->name_id ?? '-' }}</td>
                    <td class="px-4 py-3 uppercase">{{ $log->import_type }}</td>
                    <td class="px-4 py-3">{{ $log->file_name }}</td>
                    <td class="px-4 py-3">
                        <div class="text-emerald-700">Success: {{ $log->success_count }}</div>
                        <div class="text-rose-700">Failed: {{ $log->failed_count }}</div>
                        <div class="text-slate-500">Total: {{ $log->total_rows }}</div>
                    </td>
                    <td class="px-4 py-3 text-xs text-slate-600">
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
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">No import logs yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $logs->links() }}</div>
@endsection

