@extends('layouts.app', ['title' => 'Tambah Materi'])

@section('content')
<h1 class="h4 mb-3">Tambah Materi</h1>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('materials.store') }}">
        @include('materials._form')
        <button class="btn btn-primary">Simpan</button>
        <a class="btn btn-secondary" href="{{ route('materials.index') }}">Batal</a>
    </form>
</div></div>
@endsection
