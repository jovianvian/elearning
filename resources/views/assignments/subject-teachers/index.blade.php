@extends('layouts.app', ['title' => 'Subject Teacher Assignments'])
@section('content')
<div class="flex justify-between"><h2 class="text-xl font-semibold">Subject Teacher Assignments</h2><a href="{{ route('assignments.subject-teachers.create') }}" class="px-3 py-2 bg-primary text-white rounded">Assign</a></div>
<div class="bg-white rounded shadow overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-3 text-left">Teacher</th><th class="p-3">NIP</th><th class="p-3">Subject</th><th class="p-3">Academic Year</th><th class="p-3">Active</th><th class="p-3">Action</th></tr></thead><tbody>
@foreach($assignments as $a)
<tr class="border-t"><td class="p-3">{{ $a->teacher?->full_name }}</td><td class="p-3 text-center">{{ $a->teacher?->nip }}</td><td class="p-3 text-center">{{ $a->subject?->name_id }}</td><td class="p-3 text-center">{{ $a->academicYear?->name }}</td><td class="p-3 text-center">{{ $a->is_active ? 'Yes':'No' }}</td><td class="p-3 text-center"><a class="text-amber-600" href="{{ route('assignments.subject-teachers.edit', $a) }}">Edit</a> <form method="POST" class="inline" action="{{ route('assignments.subject-teachers.destroy', $a) }}">@csrf @method('DELETE')<button class="text-red-600" onclick="return confirm('Delete?')">Delete</button></form></td></tr>
@endforeach
</tbody></table></div>
{{ $assignments->links() }}
@endsection
