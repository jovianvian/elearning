@extends('layouts.app', ['title' => __('ui.edit') . ' ' . __('ui.users')])
@section('content')
<x-ui.page-header :title="__('ui.edit') . ' ' . __('ui.users')" :subtitle="__('ui.edit_user_subtitle')" />
<form method="POST" action="{{ route('users.update', $user) }}" class="tera-card">
    <div class="tera-card-body space-y-5">
        @method('PUT')
        @include('users._form')
        <div class="flex items-center gap-2">
            <button class="tera-btn tera-btn-primary">{{ __('ui.update_user') }}</button>
            <a href="{{ route('users.index') }}" class="tera-btn tera-btn-muted">{{ __('ui.back') }}</a>
        </div>
    </div>
</form>
@endsection
