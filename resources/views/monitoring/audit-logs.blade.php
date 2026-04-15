@extends('layouts.app', ['title' => __('ui.audit_logs')])

@section('content')
<div data-async-list data-fragment="#audit-logs-fragment">
    <x-ui.page-header :title="__('ui.system_audit_logs_title')" :subtitle="__('ui.system_audit_logs_subtitle')" />

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_actor_action_entity')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.entity') }}</label>
                <select name="entity_type" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($entityTypes as $entityType)
                        <option value="{{ $entityType }}" @selected(request('entity_type') === $entityType)>{{ $entityType }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div id="audit-logs-fragment">
        <div class="tera-table-wrap">
            <table class="tera-table">
                <thead>
                    <tr>
                        <th>{{ __('ui.no') }}</th>
                        <th>{{ __('ui.time') }}</th>
                        <th>{{ __('ui.actor') }}</th>
                        <th>{{ __('ui.action') }}</th>
                        <th>{{ __('ui.entity') }}</th>
                        <th>{{ __('ui.entity_id') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $loop->index }}</td>
                            <td class="text-xs">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                            <td>{{ $log->user?->full_name ?? __('ui.system') }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->entity_type }}</td>
                            <td>{{ $log->entity_id }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-slate-500">{{ __('ui.no_audit_logs_yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
