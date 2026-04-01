@extends('layouts.app', ['title' => 'Courses'])

@section('content')
<x-ui.page-header title="Course Management" subtitle="Manage course instances by subject, class, academic year, and semester.">
    <x-slot:actions>
        @if(auth()->user()->hasRole('super_admin','admin'))
            <a href="{{ route('courses.create') }}" class="tera-btn tera-btn-primary">
                <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.add_course') }}
            </a>
        @endif
    </x-slot:actions>
</x-ui.page-header>

<x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search course, subject, or class">
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
            <label class="tera-label">{{ __('ui.classes') }}</label>
            <select name="class_id" class="tera-select">
                <option value="">All</option>
                @foreach($classes as $klass)
                    <option value="{{ $klass->id }}" @selected((string)request('class_id') === (string)$klass->id)>{{ $klass->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="tera-label">{{ __('ui.academic_year') }}</label>
            <select name="academic_year_id" class="tera-select">
                <option value="">All</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" @selected((string)request('academic_year_id') === (string)$year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="tera-label">{{ __('ui.semester') }}</label>
            <select name="semester_id" class="tera-select">
                <option value="">All</option>
                @foreach($semesters as $semester)
                    <option value="{{ $semester->id }}" @selected((string)request('semester_id') === (string)$semester->id)>{{ $semester->name }}</option>
                @endforeach
            </select>
        </div>
    </x-slot:filters>
</x-ui.table-toolbar>

<div class="tera-table-wrap">
    <table class="tera-table">
        <thead>
            <tr>
                <th>No</th>
                <th>{{ __('ui.name') }}</th>
                <th>{{ __('ui.subjects') }}</th>
                <th>{{ __('ui.classes') }}</th>
                <th>{{ __('ui.academic_year') }}</th>
                <th>{{ __('ui.semester') }}</th>
                <th>{{ __('ui.teachers') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('ui.action') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($courses as $course)
            <tr>
                <td>{{ $courses->firstItem() + $loop->index }}</td>
                <td>{{ $course->title }}</td>
                <td>{{ $course->subject?->name_id }}</td>
                <td>{{ $course->schoolClass?->name }}</td>
                <td>{{ $course->academicYear?->name }}</td>
                <td>{{ $course->semester?->name }}</td>
                <td>{{ $course->teachers->count() }}</td>
                <td>
                    <span class="tera-badge {{ $course->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                        {{ $course->is_published ? __('ui.published') : __('ui.draft') }}
                    </span>
                </td>
                <td>
                    <div class="inline-flex items-center gap-2">
                        <a href="{{ route('courses.show', $course) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">{{ __('ui.view') }}</a>
                        @if(auth()->user()->hasRole('super_admin','admin'))
                            <a href="{{ route('courses.edit', $course) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">{{ __('ui.edit') }}</a>
                            <form class="inline" method="POST" action="{{ route('courses.destroy', $course) }}">
                                @csrf
                                @method('DELETE')
                                <button class="tera-btn tera-btn-danger !px-3 !py-1.5">{{ __('ui.delete') }}</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $courses->links() }}</div>
@endsection


