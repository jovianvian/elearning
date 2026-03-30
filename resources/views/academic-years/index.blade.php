@extends('layouts.app', ['title' => 'Academic Years'])
@section('content')
<div class="flex justify-between"><h2 class="text-xl font-semibold">Academic Years</h2><a href="{{ route('super-admin.academic-years.create') }}" class="px-3 py-2 bg-primary text-white rounded">Add</a></div>
<div class="bg-white rounded shadow overflow-x-auto">
<table class="w-full text-sm"><thead class="bg-slate-100"><tr><th class="p-3 text-left">Name</th><th class="p-3">Start</th><th class="p-3">End</th><th class="p-3">Active</th><th class="p-3">Action</th></tr></thead><tbody>
@foreach($academicYears as $year)
<tr class="border-t"><td class="p-3">{{ $year->name }}</td><td class="p-3 text-center">{{ $year->start_date }}</td><td class="p-3 text-center">{{ $year->end_date }}</td><td class="p-3 text-center">{{ $year->is_active ? 'Yes':'No' }}</td><td class="p-3 text-center"><a class="text-amber-600" href="{{ route('super-admin.academic-years.edit', $year) }}">Edit</a> <form class="inline" method="POST" action="{{ route('super-admin.academic-years.destroy', $year) }}">@csrf @method('DELETE') <button class="text-red-600" onclick="return confirm('Delete?')">Delete</button></form></td></tr>
@endforeach
</tbody></table></div>
{{ $academicYears->links() }}
@endsection
