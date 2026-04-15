@extends('layouts.app', ['title' => __('ui.update_semester')])

@section('content')
    <x-ui.page-header :title="__('ui.update_semester')" :subtitle="__('ui.semester_management_subtitle')">
        <x-slot:actions>
            <a href="{{ route('super-admin.semesters.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.back') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tera-card">
        <div class="tera-card-body">
            <form method="POST" action="{{ route('super-admin.semesters.update', $semester) }}" class="space-y-4" data-submit-lock="true">
                @method('PUT')
                @include('semesters._form')
                <div class="flex items-center gap-2">
                    <button class="tera-btn tera-btn-primary" data-loading-text="{{ __('ui.processing') }}">{{ __('ui.update_semester') }}</button>
                    <a href="{{ route('super-admin.semesters.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
