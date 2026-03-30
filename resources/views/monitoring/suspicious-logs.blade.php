@extends('layouts.app', ['title' => 'Suspicious Activity Logs'])

@section('content')
    <div>
        <h2 class="text-xl font-semibold">Suspicious Activity Logs</h2>
        <p class="text-sm text-slate-500">Monitoring events during exam attempts.</p>
    </div>

    <div class="bg-white border rounded-xl overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-3 text-left">Time</th>
                <th class="p-3 text-left">Student</th>
                <th class="p-3 text-left">Exam</th>
                <th class="p-3 text-left">Activity</th>
                <th class="p-3 text-left">Severity</th>
                <th class="p-3 text-left">Note</th>
            </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr class="border-t border-slate-100">
                    <td class="p-3 text-xs">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                    <td class="p-3">{{ $log->user?->full_name ?? '-' }}</td>
                    <td class="p-3">{{ $log->attempt?->exam?->title ?? '-' }}</td>
                    <td class="p-3">{{ $log->activity_type }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs {{ $log->severity === 'high' ? 'bg-red-100 text-red-700' : ($log->severity === 'medium' ? 'bg-yellow-100 text-amber-700' : 'bg-slate-100 text-slate-700') }}">
                            {{ $log->severity }}
                        </span>
                    </td>
                    <td class="p-3 text-xs">{{ $log->note }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-6 text-center text-slate-500">No suspicious activity logs.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $logs->links() }}
@endsection

