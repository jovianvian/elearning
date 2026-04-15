@extends('layouts.app', ['title' => __('ui.add_course')])

@section('content')
    <x-ui.page-header :title="__('ui.add_course')" :subtitle="__('ui.course_management_subtitle')">
        <x-slot:actions>
            <a href="{{ route('courses.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.back') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tera-card">
        <div class="tera-card-body">
            <form method="POST" action="{{ route('courses.store') }}" class="space-y-4" data-submit-lock="true">
                @include('courses._form')
                <div class="flex items-center gap-2">
                    <button class="tera-btn tera-btn-primary" data-loading-text="{{ __('ui.saving') }}">{{ __('ui.save') }}</button>
                    <a href="{{ route('courses.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
