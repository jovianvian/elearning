@extends('layouts.app', ['title' => 'Teacher Dashboard'])

@section('content')
    @php
        $teacherOverviewChart = [
            'type' => 'radar',
            'data' => [
                'labels' => ['Courses', 'Scheduled Exams', 'Needs Grading', 'Question Banks', 'Attempts'],
                'datasets' => [[
                    'label' => 'Teacher Load',
                    'data' => [
                        (int) $stats['courses'],
                        (int) $stats['scheduled_exams'],
                        (int) $stats['needs_grading'],
                        (int) $stats['question_banks'],
                        (int) $stats['total_attempts'],
                    ],
                    'backgroundColor' => 'rgba(29,78,216,.18)',
                    'borderColor' => '#1D4ED8',
                    'pointBackgroundColor' => '#1D4ED8',
                ]],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'animation' => false,
            ],
        ];
    @endphp

    <x-ui.page-header title="Teacher Dashboard" subtitle="Produktivitas mengajar: course aktif, status ujian, dan antrean penilaian.">
        <x-slot:actions>
            <a href="{{ route('question-banks.index') }}" class="tera-btn tera-btn-muted"><i data-lucide="library-big" class="w-4 h-4"></i>Question Banks</a>
            <a href="{{ route('exam-grading.index') }}" class="tera-btn tera-btn-primary"><i data-lucide="check-check" class="w-4 h-4"></i>Grading Queue</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid md:grid-cols-2 xl:grid-cols-5 gap-4">
        <x-ui.stat-card title="My Courses" :value="$stats['courses']" icon="folders" color="primary" />
        <x-ui.stat-card title="Scheduled / Active Exams" :value="$stats['scheduled_exams']" icon="calendar-clock" color="yellow" />
        <x-ui.stat-card title="Needs Grading" :value="$stats['needs_grading']" icon="file-warning" color="red" />
        <x-ui.stat-card title="Question Banks" :value="$stats['question_banks']" icon="library-big" color="sky" />
        <x-ui.stat-card title="Total Attempts" :value="$stats['total_attempts']" icon="file-check-2" color="green" />
    </div>

    <div class="tera-card">
        <div class="tera-card-body">
            <h3 class="font-bold text-sm mb-3">Teaching Overview</h3>
            <div class="relative h-72 overflow-hidden rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                <canvas class="w-full h-full" data-chart='@json($teacherOverviewChart)'></canvas>
            </div>
        </div>
    </div>
@endsection
