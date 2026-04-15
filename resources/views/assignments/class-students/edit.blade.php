@extends('layouts.app', ['title' => __('ui.class_student_assignments_title')])

@section('content')
    <x-ui.page-header :title="__('ui.class_student_assignments_title')" :subtitle="__('ui.class_student_assignments_subtitle')">
        <x-slot:actions>
            <a href="{{ route('assignments.class-students.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.back') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tera-card">
        <div class="tera-card-body">
            <form method="POST" action="{{ route('assignments.class-students.update', $class_student) }}" class="space-y-4" data-submit-lock="true">
                @method('PUT')
                @include('assignments.class-students._form')
                <div class="flex items-center gap-2">
                    <button class="tera-btn tera-btn-primary" data-loading-text="{{ __('ui.processing') }}">{{ __('ui.edit') }}</button>
                    <a href="{{ route('assignments.class-students.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
