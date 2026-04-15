@extends('layouts.app', ['title' => __('ui.update_academic_year')])

@section('content')
    <x-ui.page-header :title="__('ui.update_academic_year')" :subtitle="__('ui.academic_year_management_subtitle')">
        <x-slot:actions>
            <a href="{{ route('super-admin.academic-years.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.back') }}</a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="tera-card">
        <div class="tera-card-body">
            <form method="POST" action="{{ route('super-admin.academic-years.update', $academicYear) }}" class="space-y-4" data-submit-lock="true">
                @method('PUT')
                @include('academic-years._form')

                <div class="flex items-center gap-2">
                    <button class="tera-btn tera-btn-primary" data-loading-text="{{ __('ui.processing') }}">{{ __('ui.update_academic_year') }}</button>
                    <a href="{{ route('super-admin.academic-years.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
