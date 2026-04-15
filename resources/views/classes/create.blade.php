@extends('layouts.app', ['title' => __('ui.create_class')])
@section('content')
<x-ui.page-header :title="__('ui.create_class')" :subtitle="__('ui.create_class_subtitle')" />
<form method="POST" action="{{ route('classes.store') }}" class="tera-card">
    <div class="tera-card-body space-y-5">
        @include('classes._form')
        <div class="flex items-center gap-2">
            <button class="tera-btn tera-btn-primary">{{ __('ui.save_class') }}</button>
            <a href="{{ route('classes.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.back') }}</a>
        </div>
    </div>
</form>
@endsection
