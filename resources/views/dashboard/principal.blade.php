@extends('layouts.app', ['title' => __('ui.dashboard_principal')])

@section('content')
    @php
        $classPerformanceLabels = collect($stats['class_performance'])->pluck('class_name')->all();
        $classPerformanceData = collect($stats['class_performance'])->map(fn ($x) => round((float) $x->avg_score, 2))->all();

        $subjectPerformanceLabels = collect($stats['subject_performance'])->pluck('subject_name')->all();
        $subjectPerformanceData = collect($stats['subject_performance'])->map(fn ($x) => round((float) $x->avg_score, 2))->all();

        $classPerformanceChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $classPerformanceLabels,
                'datasets' => [[
                'label' => __('ui.average_score'),
                    'data' => $classPerformanceData,
                    'backgroundColor' => '#1D4ED8',
                    'borderRadius' => 8,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true, 'max' => 100]],
                'responsive' => true,
                'maintainAspectRatio' => false,
                'animation' => false,
            ],
        ];

        $subjectPerformanceChart = [
            'type' => 'line',
            'data' => [
                'labels' => $subjectPerformanceLabels,
                'datasets' => [[
                'label' => __('ui.average_score'),
                    'data' => $subjectPerformanceData,
                    'borderColor' => '#38BDF8',
                    'backgroundColor' => 'rgba(56,189,248,.2)',
                    'fill' => true,
                    'tension' => 0.32,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true, 'max' => 100]],
                'responsive' => true,
                'maintainAspectRatio' => false,
                'animation' => false,
            ],
        ];
    @endphp

    <x-ui.page-header title="{{ __('ui.dashboard_principal') }}" subtitle="{{ __('ui.subtitle_principal_dashboard') }}" />

    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
        <x-ui.stat-card :title="__('ui.students')" :value="$stats['students']" icon="graduation-cap" color="primary" />
        <x-ui.stat-card :title="__('ui.teachers')" :value="$stats['teachers']" icon="user-square-2" color="deep" />
        <x-ui.stat-card :title="__('ui.courses_total')" :value="$stats['courses']" icon="folders" color="sky" />
        <x-ui.stat-card :title="__('ui.exam_attempts')" :value="$stats['attempts']" icon="file-check-2" color="yellow" />
        <x-ui.stat-card :title="__('ui.average_score')" :value="$stats['avg_score']" icon="gauge" color="green" />
        <x-ui.stat-card :title="__('ui.suspicious_summary')" :value="$stats['suspicious_summary']" icon="shield-alert" color="red" />
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        <div class="tera-card">
            <div class="tera-card-body">
                <h3 class="font-bold text-sm mb-3">{{ __('ui.class_performance') }}</h3>
                <div class="relative h-72 overflow-hidden rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                    <canvas class="w-full h-full" data-chart='@json($classPerformanceChart)'></canvas>
                </div>
            </div>
        </div>
        <div class="tera-card">
            <div class="tera-card-body">
                <h3 class="font-bold text-sm mb-3">{{ __('ui.subject_performance') }}</h3>
                <div class="relative h-72 overflow-hidden rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                    <canvas class="w-full h-full" data-chart='@json($subjectPerformanceChart)'></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection
