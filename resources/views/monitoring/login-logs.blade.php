@extends('layouts.app', ['title' => 'Login Logs'])

@section('content')
    <div>
        <h2 class="text-xl font-semibold">Login Logs</h2>
        <p class="text-sm text-slate-500">Authentication history and session information.</p>
    </div>

    <div class="bg-white border rounded-xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-3 text-left">Time</th>
                <th class="p-3 text-left">User</th>
                <th class="p-3 text-left">Success</th>
                <th class="p-3 text-left">IP</th>
                <th class="p-3 text-left">Session</th>
            </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr class="border-t border-slate-100">
                    <td class="p-3 text-xs">{{ $log->login_at?->format('d M Y H:i:s') }}</td>
                    <td class="p-3">{{ $log->user?->full_name ?? '-' }}</td>
                    <td class="p-3">{{ $log->is_success ? 'Yes' : 'No' }}</td>
                    <td class="p-3">{{ $log->ip_address ?? '-' }}</td>
                    <td class="p-3 text-xs">{{ $log->session_id ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center text-slate-500">No login logs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
@endsection

