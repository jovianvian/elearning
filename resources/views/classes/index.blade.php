@extends('layouts.app', ['title' => 'Classes'])
@section('content')
<x-ui.page-header title="Classes" subtitle="Kelola struktur kelas aktif untuk tahun ajaran berjalan.">
    <x-slot:actions><a href="{{ route('classes.create') }}" class="tera-btn tera-btn-primary"><i data-lucide="plus" class="w-4 h-4"></i>Add Class</a></x-slot:actions>
</x-ui.page-header>
<div class="tera-table-wrap">
    <table class="tera-table">
        <thead><tr><th class="text-left">Name</th><th class="text-center">Grade</th><th class="text-center">Academic Year</th><th class="text-center">Homeroom</th><th class="text-center">Active</th><th class="text-right">Action</th></tr></thead>
        <tbody>
        @foreach($classes as $class)
            <tr>
                <td class="font-semibold">{{ $class->name }}</td>
                <td class="text-center">{{ $class->grade_level }}</td>
                <td class="text-center">{{ $class->academicYear?->name }}</td>
                <td class="text-center">{{ $class->homeroomTeacher?->full_name ?: '-' }}</td>
                <td class="text-center"><span class="tera-badge {{ $class->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $class->is_active ? 'Active':'Inactive' }}</span></td>
                <td class="text-right">
                    <a class="tera-btn tera-btn-muted !px-3 !py-1.5" href="{{ route('classes.edit',$class) }}">Edit</a>
                    <form class="inline" method="POST" action="{{ route('classes.destroy',$class) }}">@csrf @method('DELETE')<button class="tera-btn tera-btn-danger !px-3 !py-1.5">Delete</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $classes->links() }}
@endsection
