@extends('layouts.app', ['title' => 'Create User'])
@section('content')
<x-ui.page-header title="Create User" subtitle="Tambahkan akun baru sesuai role dan struktur akademik." />
<form method="POST" action="{{ route('users.store') }}" class="tera-card">
    <div class="tera-card-body space-y-5">
        @include('users._form')
        <div class="flex items-center gap-2">
            <button class="tera-btn tera-btn-primary">Save User</button>
            <a href="{{ route('users.index') }}" class="tera-btn tera-btn-muted">Back</a>
        </div>
    </div>
</form>
@endsection
