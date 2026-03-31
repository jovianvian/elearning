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

<div class="tera-table-wrap">
    <table class="tera-table">
        <thead>
            <tr>
                <th class="text-left">{{ __('ui.name') }}</th>
                <th class="text-left">{{ __('ui.subjects') }}</th>
                <th class="text-center">{{ __('ui.classes') }}</th>
                <th class="text-center">{{ __('ui.academic_year') }}</th>
                <th class="text-center">{{ __('ui.semester') }}</th>
                <th class="text-center">{{ __('ui.teachers') }}</th>
                <th class="text-center">{{ __('ui.status') }}</th>
                <th class="text-right">{{ __('ui.action') }}</th>
            </tr>
        </thead>
        <tbody>
        @foreach($courses as $course)
            <tr>
                <td>{{ $course->title }}</td>
                <td>{{ $course->subject?->name_id }}</td>
                <td class="text-center">{{ $course->schoolClass?->name }}</td>
                <td class="text-center">{{ $course->academicYear?->name }}</td>
                <td class="text-center">{{ $course->semester?->name }}</td>
                <td class="text-center">{{ $course->teachers->count() }}</td>
                <td class="text-center">
                    <span class="tera-badge {{ $course->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                        {{ $course->is_published ? __('ui.published') : __('ui.draft') }}
                    </span>
                </td>
                <td class="text-right">
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


