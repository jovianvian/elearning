@extends('layouts.app', ['title' => 'Subjects'])
@section('content')
<x-ui.page-header title="Subjects" subtitle="Master data mata pelajaran bilingual untuk struktur akademik SMP.">
    <x-slot:actions><a href="{{ route('subjects.create') }}" class="tera-btn tera-btn-primary"><i data-lucide="plus" class="w-4 h-4"></i>Add Subject</a></x-slot:actions>
</x-ui.page-header>

<div class="tera-table-wrap">
    <table class="tera-table">
        <thead>
            <tr><th class="text-left">Name ID</th><th class="text-left">Name EN</th><th class="text-center">Code</th><th class="text-center">Active</th><th class="text-right">Action</th></tr>
        </thead>
        <tbody>
        @foreach($subjects as $subject)
            <tr>
                <td class="font-semibold">{{ $subject->name_id }}</td>
                <td>{{ $subject->name_en ?: '-' }}</td>
                <td class="text-center">{{ $subject->code }}</td>
                <td class="text-center"><span class="tera-badge {{ $subject->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $subject->is_active ? 'Active':'Inactive' }}</span></td>
                <td class="text-right">
                    <a class="tera-btn tera-btn-muted !px-3 !py-1.5" href="{{ route('subjects.edit',$subject) }}">Edit</a>
                    <form class="inline" method="POST" action="{{ route('subjects.destroy',$subject) }}">@csrf @method('DELETE')<button class="tera-btn tera-btn-danger !px-3 !py-1.5">Delete</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $subjects->links() }}
@endsection
