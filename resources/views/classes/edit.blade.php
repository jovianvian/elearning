@extends('layouts.app', ['title' => __('ui.edit') . ' ' . __('ui.classes')])
@section('content')
<x-ui.page-header :title="__('ui.edit') . ' ' . __('ui.classes')" :subtitle="__('ui.edit_class_subtitle')" />
<form method="POST" action="{{ route('classes.update', $schoolClass) }}" class="tera-card">
    <div class="tera-card-body space-y-5">
        @method('PUT')
        @include('classes._form')
        <div class="flex items-center gap-2">
            <button class="tera-btn tera-btn-primary">{{ __('ui.update_class') }}</button>
            <a href="{{ route('classes.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.back') }}</a>
        </div>
    </div>
</form>
@endsection
