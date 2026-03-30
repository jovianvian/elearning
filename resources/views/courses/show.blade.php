@extends('layouts.app', ['title' => 'Course Detail'])
@section('content')
<div class="bg-white rounded shadow p-4 space-y-4">
<h2 class="text-xl font-semibold">{{ $course->title }}</h2>
<div class="grid md:grid-cols-2 gap-3 text-sm">
<div><span class="text-slate-500">Subject:</span> {{ $course->subject?->name_id }}</div>
<div><span class="text-slate-500">Class:</span> {{ $course->schoolClass?->name }}</div>
<div><span class="text-slate-500">Academic Year:</span> {{ $course->academicYear?->name }}</div>
<div><span class="text-slate-500">Semester:</span> {{ $course->semester?->name }}</div>
<div><span class="text-slate-500">Published:</span> {{ $course->is_published ? 'Yes' : 'No' }}</div>
</div>
@if(auth()->user()->hasRole('super_admin','admin'))
<form method="POST" action="{{ route('courses.sync-students', $course) }}">@csrf <button class="px-3 py-2 bg-skyx text-white rounded">Sync Students from Class</button></form>
@endif
<div>
<h3 class="font-semibold mb-2">Teachers</h3>
<ul class="list-disc ml-5 text-sm">@foreach($course->teachers as $teacher)<li>{{ $teacher->full_name }} @if($teacher->pivot->is_main_teacher)<span class="text-xs text-primary">(Main)</span>@endif</li>@endforeach</ul>
</div>
<div>
<h3 class="font-semibold mb-2">Students</h3>
<div class="max-h-64 overflow-auto border rounded">
<table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-2 text-left">Name</th><th class="p-2">NIS</th></tr></thead><tbody>
@foreach($course->students as $student)
<tr class="border-t"><td class="p-2">{{ $student->full_name }}</td><td class="p-2 text-center">{{ $student->nis }}</td></tr>
@endforeach
</tbody></table>
</div>
</div>
</div>
@endsection
