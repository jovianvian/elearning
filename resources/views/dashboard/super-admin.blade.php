@extends('layouts.app', ['title' => 'Super Admin Dashboard'])

@section('content')
    @php
        $distributionChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => ['Users', 'Courses', 'Exams', 'Suspicious'],
                'datasets' => [[
                    'data' => [(int) $stats['users'], (int) $stats['courses'], (int) $stats['exams'], (int) $stats['suspicious']],
                    'backgroundColor' => ['#1D4ED8', '#1E3A8A', '#FACC15', '#DC2626'],
                ]],
            ],
            'options' => [
                'plugins' => [
                    'legend' => ['position' => 'bottom'],
                ],
                'responsive' => true,
                'maintainAspectRatio' => false,
                'animation' => false,
            ],
        ];
    @endphp

    <x-ui.page-header title="Super Admin Dashboard" subtitle="System-wide control center, logs, restore, and configuration overview.">
        <x-slot:actions>
            <a href="{{ route('super-admin.settings.edit') }}" class="tera-btn tera-btn-primary"><i data-lucide="settings-2" class="w-4 h-4"></i>Settings</a>
            <a href="{{ route('super-admin.restore-center.index') }}" class="tera-btn tera-btn-muted"><i data-lucide="rotate-ccw" class="w-4 h-4"></i>Restore Center</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-ui.stat-card title="Users" :value="$stats['users']" icon="users" color="primary" />
        <x-ui.stat-card title="Courses" :value="$stats['courses']" icon="folders" color="deep" />
        <x-ui.stat-card title="Exams" :value="$stats['exams']" icon="notepad-text" color="yellow" />
        <x-ui.stat-card title="Suspicious Logs" :value="$stats['suspicious']" icon="shield-alert" color="red" />
    </div>

    <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-ui.stat-card title="Deleted Items" :value="$stats['deleted_items']" icon="trash-2" color="sky" />
        <x-ui.stat-card title="Audit Logs" :value="$stats['audit_logs']" icon="scroll-text" color="deep" />
        <x-ui.stat-card title="Login Logs" :value="$stats['login_logs']" icon="fingerprint" color="primary" />
        <x-ui.stat-card title="Active Sessions" :value="$stats['active_sessions']" icon="activity" color="green" />
    </div>

    <div class="grid lg:grid-cols-[1.2fr_.8fr] gap-4">
        <div class="tera-card">
            <div class="tera-card-body">
                <h3 class="font-bold text-sm mb-3">System Distribution</h3>
                <div class="relative w-full overflow-hidden rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                    <div class="relative mx-auto max-w-[360px] aspect-square">
                    <canvas
                        class="w-full h-full"
                        data-chart='@json($distributionChart)'
                    ></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="tera-card">
            <div class="tera-card-body text-sm space-y-2">
                <h3 class="font-bold text-sm mb-2">Branding & Active Period</h3>
                <div class="flex justify-between"><span class="text-slate-500">App</span><span class="font-semibold">{{ $stats['app_name'] }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">School</span><span class="font-semibold">{{ $stats['school_name'] }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Academic Year</span><span class="font-semibold">{{ $stats['active_year'] }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Semester</span><span class="font-semibold">{{ $stats['active_semester'] }}</span></div>
                <div class="pt-3">
                    <a href="{{ route('super-admin.audit-logs.index') }}" class="tera-btn tera-btn-muted w-full justify-center">Open Audit Logs</a>
                </div>
            </div>
        </div>
    </div>
@endsection
