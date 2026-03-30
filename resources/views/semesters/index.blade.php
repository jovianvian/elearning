@extends('layouts.app', ['title' => 'Semesters'])
@section('content')
<div class="flex justify-between"><h2 class="text-xl font-semibold">Semesters</h2><a href="{{ route('super-admin.semesters.create') }}" class="px-3 py-2 bg-primary text-white rounded">Add</a></div>
<div class="bg-white rounded shadow overflow-x-auto">
<table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-3 text-left">Name</th><th class="p-3 text-left">Code</th><th class="p-3">Year</th><th class="p-3">Active</th><th class="p-3">Action</th></tr></thead><tbody>
@foreach($semesters as $semester)
<tr class="border-t"><td class="p-3">{{ $semester->name }}</td><td class="p-3">{{ $semester->code }}</td><td class="p-3 text-center">{{ $semester->academicYear?->name }}</td><td class="p-3 text-center">{{ $semester->is_active ? 'Yes':'No' }}</td><td class="p-3 text-center"><a class="text-amber-600" href="{{ route('super-admin.semesters.edit', $semester) }}">Edit</a> <form method="POST" class="inline" action="{{ route('super-admin.semesters.destroy', $semester) }}">@csrf @method('DELETE') <button class="text-red-600" onclick="return confirm('Delete?')">Delete</button></form></td></tr>
@endforeach
</tbody></table></div>
{{ $semesters->links() }}
@endsection
