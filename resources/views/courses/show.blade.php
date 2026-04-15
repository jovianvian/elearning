@extends('layouts.app', ['title' => __('ui.course_detail')])

@section('content')
<x-ui.page-header :title="__('ui.course_detail')" :subtitle="__('ui.course_detail_subtitle')">
    <x-slot:actions>
        @if(auth()->user()->hasRole('super_admin','admin'))
            <form method="POST" action="{{ route('courses.sync-students', $course) }}">
                @csrf
                <button class="tera-btn tera-btn-outline">{{ __('ui.sync_students_from_class') }}</button>
            </form>
        @endif
    </x-slot:actions>
</x-ui.page-header>

<div class="tera-card">
    <div class="tera-card-body space-y-5">
        <h2 class="tera-h1">{{ $course->title }}</h2>

        <div class="grid md:grid-cols-2 gap-3 text-sm">
            <div><span class="text-slate-500">{{ __('ui.subjects') }}:</span> {{ $course->subject?->name_id }}</div>
            <div><span class="text-slate-500">{{ __('ui.classes') }}:</span> {{ $course->schoolClass?->name }}</div>
            <div><span class="text-slate-500">{{ __('ui.academic_year') }}:</span> {{ $course->academicYear?->name }}</div>
            <div><span class="text-slate-500">{{ __('ui.semester') }}:</span> {{ $course->semester?->name }}</div>
            <div><span class="text-slate-500">{{ __('ui.published') }}:</span>
                <span class="tera-badge tera-status-badge {{ $course->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $course->is_published ? __('ui.published') : __('ui.draft') }}</span>
            </div>
        </div>

        <div>
            <h3 class="font-semibold mb-2">{{ __('ui.teachers') }}</h3>
            <ul class="list-disc ml-5 text-sm space-y-1">
                @forelse($course->teachers as $teacher)
                    <li>{{ $teacher->full_name }} @if($teacher->pivot->is_main_teacher)<span class="text-xs text-primary">({{ __('ui.main_teacher') }})</span>@endif</li>
                @empty
                    <li class="text-slate-500">{{ __('ui.no_data') }}</li>
                @endforelse
            </ul>
        </div>

        <div>
            <h3 class="font-semibold mb-2">{{ __('ui.students') }}</h3>
            <div class="tera-table-wrap">
                <table class="tera-table">
                    <thead><tr><th class="text-left">{{ __('ui.name') }}</th><th class="text-center">NIS</th></tr></thead>
                    <tbody>
                    @forelse($course->students as $student)
                        <tr><td>{{ $student->full_name }}</td><td class="text-center">{{ $student->nis }}</td></tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-slate-500">{{ __('ui.no_data') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

