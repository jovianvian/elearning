@extends('layouts.app', ['title' => 'Audit Logs'])

@section('content')
    <x-ui.page-header title="System Audit Logs" subtitle="Track important system actions, changes, and restore history." />

    <x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search actor, action, or entity">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.entity') }}</label>
                <select name="entity_type" class="tera-select">
                    <option value="">All</option>
                    @foreach($entityTypes as $entityType)
                        <option value="{{ $entityType }}" @selected(request('entity_type') === $entityType)>{{ $entityType }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Time</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Entity ID</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $logs->firstItem() + $loop->index }}</td>
                        <td class="text-xs">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                        <td>{{ $log->user?->full_name ?? 'System' }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->entity_type }}</td>
                        <td>{{ $log->entity_id }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-slate-500">No audit logs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
