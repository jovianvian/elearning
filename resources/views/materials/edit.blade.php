@extends('layouts.app', ['title' => 'Edit Materi'])

@section('content')
<h1 class="h4 mb-3">Edit Materi</h1>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('materials.update', $material) }}">
        @method('PUT')
        @include('materials._form')
        <button class="btn btn-primary">Update</button>
        <a class="btn btn-secondary" href="{{ route('materials.index') }}">Batal</a>
    </form>
</div></div>
@endsection
