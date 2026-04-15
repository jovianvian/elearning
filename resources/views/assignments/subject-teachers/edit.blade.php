@extends('layouts.app', ['title' => __('ui.subject_teacher_assignments_title')])

@section('content')
    <x-ui.page-header :title="__('ui.subject_teacher_assignments_title')" :subtitle="__('ui.subject_teacher_assignments_subtitle')">
        <x-slot:actions>
            <a href="{{ route('assignments.subject-teachers.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.back') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tera-card">
        <div class="tera-card-body">
            <form method="POST" action="{{ route('assignments.subject-teachers.update', $subject_teacher) }}" class="space-y-4" data-submit-lock="true">
                @method('PUT')
                @include('assignments.subject-teachers._form')
                <div class="flex items-center gap-2">
                    <button class="tera-btn tera-btn-primary" data-loading-text="{{ __('ui.processing') }}">{{ __('ui.edit') }}</button>
                    <a href="{{ route('assignments.subject-teachers.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
