@extends('layouts.app', ['title' => 'Login Logs'])

@section('content')
    <x-ui.page-header title="Login Activity Logs" subtitle="Monitor user login history, session access, and activity records." />

    <x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search user, IP, or session id">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.status') }}</label>
                <select name="is_success" class="tera-select">
                    <option value="">All</option>
                    <option value="1" @selected(request('is_success') === '1')>Success</option>
                    <option value="0" @selected(request('is_success') === '0')>Failed</option>
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
                    <th>User</th>
                    <th>Success</th>
                    <th>IP</th>
                    <th>Session</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $logs->firstItem() + $loop->index }}</td>
                        <td class="text-xs">{{ $log->login_at?->format('d M Y H:i:s') }}</td>
                        <td>{{ $log->user?->full_name ?? '-' }}</td>
                        <td>{{ $log->is_success ? 'Yes' : 'No' }}</td>
                        <td>{{ $log->ip_address ?? '-' }}</td>
                        <td class="text-xs">{{ $log->session_id ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-6 text-center text-slate-500">No login logs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
