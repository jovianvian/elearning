@extends('layouts.app', ['title' => 'Assignments'])

@section('content')
<x-ui.page-header title="Class Student Assignments" subtitle="Assign students to active classes by academic year.">
    <x-slot:actions>
        <a href="{{ route('assignments.class-students.create') }}" class="tera-btn tera-btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.assign_student') }}
        </a>
    </x-slot:actions>
</x-ui.page-header>

<div class="tera-table-wrap">
    <table class="tera-table">
        <thead>
            <tr>
                <th class="text-left">{{ __('ui.student') }}</th>
                <th class="text-center">NIS</th>
                <th class="text-center">{{ __('ui.classes') }}</th>
                <th class="text-center">{{ __('ui.academic_year') }}</th>
                <th class="text-center">{{ __('ui.status') }}</th>
                <th class="text-right">{{ __('ui.action') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($assignments as $a)
            <tr>
                <td>{{ $a->student?->full_name }}</td>
                <td class="text-center">{{ $a->student?->nis }}</td>
                <td class="text-center">{{ $a->class?->name }}</td>
                <td class="text-center">{{ $a->academicYear?->name }}</td>
                <td class="text-center">
                    <span class="tera-badge {{ $a->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ ucfirst($a->status) }}</span>
                </td>
                <td class="text-right">
                    <div class="inline-flex items-center gap-2">
                        <a class="tera-btn tera-btn-muted !px-3 !py-1.5" href="{{ route('assignments.class-students.edit', $a) }}">{{ __('ui.edit') }}</a>
                        <form method="POST" class="inline" action="{{ route('assignments.class-students.destroy', $a) }}">
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

