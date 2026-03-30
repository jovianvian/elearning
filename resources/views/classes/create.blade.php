@extends('layouts.app', ['title' => 'Create Class'])
@section('content')
<x-ui.page-header title="Create Class" subtitle="Tambah data kelas baru untuk operasional akademik." />
<form method="POST" action="{{ route('classes.store') }}" class="tera-card">
    <div class="tera-card-body space-y-5">
        @include('classes._form')
        <div class="flex items-center gap-2">
            <button class="tera-btn tera-btn-primary">Save Class</button>
            <a href="{{ route('classes.index') }}" class="tera-btn tera-btn-muted">Back</a>
        </div>
    </div>
</form>
@endsection
