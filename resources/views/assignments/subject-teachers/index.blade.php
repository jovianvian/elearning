@extends('layouts.app', ['title' => 'Assignments'])

@section('content')
<x-ui.page-header title="Subject Teacher Assignments" subtitle="Assign teachers to subjects for the active academic period.">
    <x-slot:actions>
        <a href="{{ route('assignments.subject-teachers.create') }}" class="tera-btn tera-btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.assign_teacher') }}
        </a>
    </x-slot:actions>
</x-ui.page-header>

<x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search teacher, NIP, or subject">
    <x-slot:filters>
        <div>
            <label class="tera-label">{{ __('ui.subjects') }}</label>
            <select name="subject_id" class="tera-select">
                <option value="">All</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string)request('subject_id') === (string)$subject->id)>{{ $subject->name_id }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="tera-label">{{ __('ui.academic_year') }}</label>
            <select name="academic_year_id" class="tera-select">
                <option value="">All</option>
                @foreach($years as $year)
                    <option value="{{ $year->id }}" @selected((string)request('academic_year_id') === (string)$year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="tera-label">{{ __('ui.active') }}</label>
            <select name="is_active" class="tera-select">
                <option value="">All</option>
                <option value="1" @selected(request('is_active') === '1')>{{ __('ui.active') }}</option>
                <option value="0" @selected(request('is_active') === '0')>{{ __('ui.inactive') }}</option>
            </select>
        </div>
    </x-slot:filters>
</x-ui.table-toolbar>

<div class="tera-table-wrap">
    <table class="tera-table">
        <thead>
            <tr>
                <th>No</th>
                <th>{{ __('ui.teacher') }}</th>
                <th>NIP</th>
                <th>{{ __('ui.subjects') }}</th>
                <th>{{ __('ui.academic_year') }}</th>
                <th>{{ __('ui.active') }}</th>
                <th>{{ __('ui.action') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($assignments as $a)
            <tr>
                <td>{{ $assignments->firstItem() + $loop->index }}</td>
                <td>{{ $a->teacher?->full_name }}</td>
                <td>{{ $a->teacher?->nip }}</td>
                <td>{{ $a->subject?->name_id }}</td>
                <td>{{ $a->academicYear?->name }}</td>
                <td>
                    <span class="tera-badge {{ $a->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $a->is_active ? __('ui.active') : __('ui.inactive') }}</span>
                </td>
                <td>
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

