@extends('layouts.app', ['title' => 'Class Student Assignments'])
@section('content')
<div class="flex justify-between"><h2 class="text-xl font-semibold">Class Student Assignments</h2><a href="{{ route('assignments.class-students.create') }}" class="px-3 py-2 bg-primary text-white rounded">Assign</a></div>
<div class="bg-white rounded shadow overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-3 text-left">Student</th><th class="p-3">NIS</th><th class="p-3">Class</th><th class="p-3">Academic Year</th><th class="p-3">Status</th><th class="p-3">Action</th></tr></thead><tbody>
@foreach($assignments as $a)
<tr class="border-t"><td class="p-3">{{ $a->student?->full_name }}</td><td class="p-3 text-center">{{ $a->student?->nis }}</td><td class="p-3 text-center">{{ $a->class?->name }}</td><td class="p-3 text-center">{{ $a->academicYear?->name }}</td><td class="p-3 text-center">{{ $a->status }}</td><td class="p-3 text-center"><a class="text-amber-600" href="{{ route('assignments.class-students.edit', $a) }}">Edit</a> <form method="POST" class="inline" action="{{ route('assignments.class-students.destroy', $a) }}">@csrf @method('DELETE')<button class="text-red-600" onclick="return confirm('Delete?')">Delete</button></form></td></tr>
@endforeach
</tbody></table></div>
{{ $assignments->links() }}
@endsection
