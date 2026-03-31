@extends('layouts.app', ['title' => 'Assignments'])

@section('content')
<x-ui.page-header title="Subject Teacher Assignments" subtitle="Assign teachers to subjects for the active academic period.">
    <x-slot:actions>
        <a href="{{ route('assignments.subject-teachers.create') }}" class="tera-btn tera-btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.assign_teacher') }}
        </a>
    </x-slot:actions>
</x-ui.page-header>

<div class="tera-table-wrap">
    <table class="tera-table">
        <thead>
            <tr>
                <th class="text-left">{{ __('ui.teacher') }}</th>
                <th class="text-center">NIP</th>
                <th class="text-center">{{ __('ui.subjects') }}</th>
                <th class="text-center">{{ __('ui.academic_year') }}</th>
                <th class="text-center">{{ __('ui.active') }}</th>
                <th class="text-right">{{ __('ui.action') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($assignments as $a)
            <tr>
                <td>{{ $a->teacher?->full_name }}</td>
                <td class="text-center">{{ $a->teacher?->nip }}</td>
                <td class="text-center">{{ $a->subject?->name_id }}</td>
                <td class="text-center">{{ $a->academicYear?->name }}</td>
                <td class="text-center">
                    <span class="tera-badge {{ $a->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $a->is_active ? __('ui.active') : __('ui.inactive') }}</span>
                </td>
                <td class="text-right">
                    <div class="inline-flex items-center gap-2">
                        <a class="tera-btn tera-btn-muted !px-3 !py-1.5" href="{{ route('assignments.subject-teachers.edit', $a) }}">{{ __('ui.edit') }}</a>
                        <form method="POST" class="inline" action="{{ route('assignments.subject-teachers.destroy', $a) }}">
                            @csrf
                            @method('DELETE')
                            <button class="tera-btn tera-btn-danger !px-3 !py-1.5">{{ __('ui.delete') }}</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $assignments->links() }}</div>
@endsection

