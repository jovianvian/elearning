@extends('layouts.app', ['title' => __('ui.login_logs')])

@section('content')
<div data-async-list data-fragment="#login-logs-fragment">
    <x-ui.page-header :title="__('ui.login_activity_logs_title')" :subtitle="__('ui.login_activity_logs_subtitle')" />

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_user_ip_session')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.status') }}</label>
                <select name="is_success" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    <option value="1" @selected(request('is_success') === '1')>{{ __('ui.success') }}</option>
                    <option value="0" @selected(request('is_success') === '0')>{{ __('ui.failed') }}</option>
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div id="login-logs-fragment">
        <div class="tera-table-wrap">
            <table class="tera-table">
                <thead>
                    <tr>
                        <th>{{ __('ui.no') }}</th>
                        <th>{{ __('ui.time') }}</th>
                        <th>{{ __('ui.users') }}</th>
                        <th>{{ __('ui.success') }}</th>
                        <th>IP</th>
                        <th>{{ __('ui.session') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $loop->index }}</td>
                            <td class="text-xs">{{ $log->login_at?->format('d M Y H:i:s') }}</td>
                            <td>{{ $log->user?->full_name ?? '-' }}</td>
                            <td>{{ $log->is_success ? __('ui.yes') : __('ui.no') }}</td>
                            <td>{{ $log->ip_address ?? '-' }}</td>
                            <td class="text-xs">{{ $log->session_id ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-slate-500">{{ __('ui.no_login_logs_yet') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
