@extends('layouts.app', ['title' => 'Edit User'])
@section('content')
<x-ui.page-header title="Edit User" subtitle="Perbarui data akun, role, dan pengaturan akses." />
<form method="POST" action="{{ route('users.update', $user) }}" class="tera-card">
    <div class="tera-card-body space-y-5">
        @method('PUT')
        @include('users._form')
        <div class="flex items-center gap-2">
            <button class="tera-btn tera-btn-primary">Update User</button>
            <a href="{{ route('users.index') }}" class="tera-btn tera-btn-muted">Back</a>
        </div>
    </div>
</form>
@endsection
