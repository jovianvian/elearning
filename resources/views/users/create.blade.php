@extends('layouts.app', ['title' => __('ui.create_user')])
@section('content')
<x-ui.page-header :title="__('ui.create_user')" :subtitle="__('ui.create_user_subtitle')" />
<form method="POST" action="{{ route('users.store') }}" class="tera-card">
    <div class="tera-card-body space-y-5">
        @include('users._form')
        <div class="flex items-center gap-2">
            <button class="tera-btn tera-btn-primary">{{ __('ui.save_user') }}</button>
            <a href="{{ route('users.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.back') }}</a>
        </div>
    </div>
</form>
@endsection
