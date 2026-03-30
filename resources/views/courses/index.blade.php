@extends('layouts.app', ['title' => 'Courses'])
@section('content')
<div class="flex justify-between items-center">
    <h2 class="text-xl font-semibold">Courses</h2>
    @if(auth()->user()->hasRole('super_admin','admin'))
        <a href="{{ route('courses.create') }}" class="px-3 py-2 bg-primary text-white rounded">Add Course</a>
    @endif
</div>
<div class="bg-white rounded shadow overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-slate-100"><tr><th class="p-3 text-left">Title</th><th class="p-3 text-left">Subject</th><th class="p-3 text-center">Class</th><th class="p-3 text-center">Year</th><th class="p-3 text-center">Semester</th><th class="p-3 text-center">Teachers</th><th class="p-3 text-center">Published</th><th class="p-3 text-center">Action</th></tr></thead>
<tbody>
@foreach($courses as $course)
<tr class="border-t">
<td class="p-3">{{ $course->title }}</td>
<td class="p-3">{{ $course->subject?->name_id }}</td>
<td class="p-3 text-center">{{ $course->schoolClass?->name }}</td>
<td class="p-3 text-center">{{ $course->academicYear?->name }}</td>
<td class="p-3 text-center">{{ $course->semester?->name }}</td>
<td class="p-3 text-center">{{ $course->teachers->count() }}</td>
<td class="p-3 text-center">{{ $course->is_published ? 'Yes':'No' }}</td>
<td class="p-3 text-center">
<a href="{{ route('courses.show', $course) }}" class="text-sky-600">View</a>
@if(auth()->user()->hasRole('super_admin','admin'))
<a href="{{ route('courses.edit', $course) }}" class="text-amber-600 ml-2">Edit</a>
<form class="inline" method="POST" action="{{ route('courses.destroy', $course) }}">@csrf @method('DELETE')<button class="text-red-600 ml-2" onclick="return confirm('Delete course?')">Delete</button></form>
@endif
</td>
</tr>
@endforeach
</tbody></table>
</div>
{{ $courses->links() }}
@endsection
