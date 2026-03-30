@extends('layouts.app', ['title' => 'Edit Class'])
@section('content')
<x-ui.page-header title="Edit Class" subtitle="Perbarui detail kelas, wali kelas, dan status aktif." />
<form method="POST" action="{{ route('classes.update', $schoolClass) }}" class="tera-card">
    <div class="tera-card-body space-y-5">
        @method('PUT')
        @include('classes._form')
        <div class="flex items-center gap-2">
            <button class="tera-btn tera-btn-primary">Update Class</button>
            <a href="{{ route('classes.index') }}" class="tera-btn tera-btn-muted">Back</a>
        </div>
    </div>
</form>
@endsection
