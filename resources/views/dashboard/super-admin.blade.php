@extends('layouts.app', ['title' => __('ui.dashboard')])

@section('content')
    @php
        $distributionChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => [__('ui.users_total'), __('ui.courses_total'), __('ui.exams_total'), __('ui.suspicious_logs_total')],
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

    <x-ui.page-header :title="__('ui.dashboard_super_admin')" :subtitle="__('ui.subtitle_super_admin_dashboard')">
        <x-slot:actions>
            <a href="{{ route('super-admin.settings.edit') }}" class="tera-btn tera-btn-primary"><i data-lucide="settings-2" class="w-4 h-4"></i>{{ __('ui.settings_button') }}</a>
            <a href="{{ route('super-admin.restore-center.index') }}" class="tera-btn tera-btn-muted"><i data-lucide="rotate-ccw" class="w-4 h-4"></i>{{ __('ui.restore_center') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-ui.stat-card :title="__('ui.users_total')" :value="$stats['users']" icon="users" color="primary" />
        <x-ui.stat-card :title="__('ui.courses_total')" :value="$stats['courses']" icon="folders" color="deep" />
        <x-ui.stat-card :title="__('ui.exams_total')" :value="$stats['exams']" icon="notepad-text" color="yellow" />
        <x-ui.stat-card :title="__('ui.suspicious_logs_total')" :value="$stats['suspicious']" icon="shield-alert" color="red" />
    </div>

    <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-ui.stat-card :title="__('ui.deleted_items')" :value="$stats['deleted_items']" icon="trash-2" color="sky" />
        <x-ui.stat-card :title="__('ui.audit_logs_total')" :value="$stats['audit_logs']" icon="scroll-text" color="deep" />
        <x-ui.stat-card :title="__('ui.login_logs_total')" :value="$stats['login_logs']" icon="fingerprint" color="primary" />
        <x-ui.stat-card :title="__('ui.active_sessions')" :value="$stats['active_sessions']" icon="activity" color="green" />
    </div>

    <div class="grid lg:grid-cols-[1.2fr_.8fr] gap-4">
        <div class="tera-card">
            <div class="tera-card-body">
                <h3 class="font-bold text-sm mb-3">{{ __('ui.system_distribution') }}</h3>
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
                <h3 class="font-bold text-sm mb-2">{{ __('ui.branding_active_period') }}</h3>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('ui.app') }}</span><span class="font-semibold">{{ $stats['app_name'] }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('ui.school') }}</span><span class="font-semibold">{{ $stats['school_name'] }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('ui.academic_year') }}</span><span class="font-semibold">{{ $stats['active_year'] }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('ui.semester') }}</span><span class="font-semibold">{{ $stats['active_semester'] }}</span></div>
                <div class="pt-3">
                    <a href="{{ route('super-admin.audit-logs.index') }}" class="tera-btn tera-btn-muted w-full justify-center">{{ __('ui.open_audit_logs') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
