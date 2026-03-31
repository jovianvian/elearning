@extends('layouts.app', ['title' => 'Audit Logs'])

@section('content')
    <x-ui.page-header title="System Audit Logs" subtitle="Track important system actions, changes, and restore history." />

    <div class="bg-white border rounded-xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-3 text-left">Time</th>
                <th class="p-3 text-left">Actor</th>
                <th class="p-3 text-left">Action</th>
                <th class="p-3 text-left">Entity</th>
                <th class="p-3 text-left">Entity ID</th>
            </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr class="border-t border-slate-100">
                    <td class="p-3 text-xs">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                    <td class="p-3">{{ $log->user?->full_name ?? 'System' }}</td>
                    <td class="p-3">{{ $log->action }}</td>
                    <td class="p-3">{{ $log->entity_type }}</td>
                    <td class="p-3">{{ $log->entity_id }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center text-slate-500">No audit logs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
@endsection
