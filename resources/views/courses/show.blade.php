@extends('layouts.app', ['title' => 'Course Detail'])

@section('content')
<x-ui.page-header title="Course Detail" subtitle="Detail course, teacher assignments, and enrolled students.">
    <x-slot:actions>
        @if(auth()->user()->hasRole('super_admin','admin'))
            <form method="POST" action="{{ route('courses.sync-students', $course) }}">
                @csrf
                <button class="tera-btn tera-btn-outline">Sync Students from Class</button>
            </form>
        @endif
    </x-slot:actions>
</x-ui.page-header>

<div class="tera-card">
    <div class="tera-card-body space-y-5">
        <h2 class="tera-h1">{{ $course->title }}</h2>

        <div class="grid md:grid-cols-2 gap-3 text-sm">
            <div><span class="text-slate-500">Subject:</span> {{ $course->subject?->name_id }}</div>
            <div><span class="text-slate-500">Class:</span> {{ $course->schoolClass?->name }}</div>
            <div><span class="text-slate-500">Academic Year:</span> {{ $course->academicYear?->name }}</div>
            <div><span class="text-slate-500">Semester:</span> {{ $course->semester?->name }}</div>
            <div><span class="text-slate-500">Published:</span>
                <span class="tera-badge {{ $course->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $course->is_published ? 'Published' : 'Draft' }}</span>
            </div>
        </div>

        <div>
            <h3 class="font-semibold mb-2">Teachers</h3>
            <ul class="list-disc ml-5 text-sm space-y-1">
                @foreach($course->teachers as $teacher)
                    <li>{{ $teacher->full_name }} @if($teacher->pivot->is_main_teacher)<span class="text-xs text-primary">(Main)</span>@endif</li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3 class="font-semibold mb-2">Students</h3>
            <div class="tera-table-wrap">
                <table class="tera-table">
                    <thead><tr><th class="text-left">Name</th><th class="text-center">NIS</th></tr></thead>
                    <tbody>
                    @foreach($course->students as $student)
                        <tr><td>{{ $student->full_name }}</td><td class="text-center">{{ $student->nis }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
