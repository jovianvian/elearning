@extends('layouts.app', ['title' => 'Admin Dashboard'])

@section('content')
    @php
        $adminActivityChart = [
            'type' => 'bar',
            'data' => [
                'labels' => ['Today Logins', 'Today Attempts', 'Active Exams'],
                'datasets' => [[
                    'label' => 'Count',
                    'data' => [(int) $stats['today_logins'], (int) $stats['today_attempts'], (int) $stats['active_exams']],
                    'backgroundColor' => ['#1D4ED8', '#38BDF8', '#FACC15'],
                    'borderRadius' => 8,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true]],
                'responsive' => false,
                'maintainAspectRatio' => false,
                'animation' => false,
            ],
        ];
    @endphp

    <x-ui.page-header title="Admin Dashboard" subtitle="Operational overview untuk data pengguna, akademik, dan aktivitas harian.">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="tera-btn tera-btn-primary"><i data-lucide="users" class="w-4 h-4"></i>Manage Users</a>
            <a href="{{ route('courses.index') }}" class="tera-btn tera-btn-muted"><i data-lucide="folders" class="w-4 h-4"></i>Open Courses</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
        <x-ui.stat-card title="Total Users" :value="$stats['users']" icon="users" color="primary" />
        <x-ui.stat-card title="Active Users" :value="$stats['active_users']" icon="user-check" color="green" />
        <x-ui.stat-card title="Inactive Users" :value="$stats['inactive_users']" icon="user-minus" color="deep" />
        <x-ui.stat-card title="Blocked Users" :value="$stats['blocked']" icon="shield-x" color="red" />
        <x-ui.stat-card title="Classes" :value="$stats['classes']" icon="school" color="sky" />
        <x-ui.stat-card title="Courses" :value="$stats['courses']" icon="folders" color="primary" />
        <x-ui.stat-card title="Active Exams" :value="$stats['active_exams']" icon="notepad-text" color="yellow" />
        <x-ui.stat-card title="Today Logins" :value="$stats['today_logins']" icon="log-in" color="deep" />
        <x-ui.stat-card title="Today Attempts" :value="$stats['today_attempts']" icon="pen-line" color="green" />
    </div>

    <div class="tera-card">
        <div class="tera-card-body">
            <h3 class="font-bold text-sm mb-3">Daily Activity Snapshot</h3>
            <div class="relative w-full overflow-hidden rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                <canvas
                    width="900"
                    height="280"
                    style="display:block;width:100%;height:280px;max-height:280px;"
                    data-chart='@json($adminActivityChart)'
                ></canvas>
            </div>
        </div>
    </div>
@endsection
