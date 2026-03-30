@extends('layouts.app', ['title' => $material->title])

@section('content')
<div class="card">
    <div class="card-body">
        <h1 class="h4">{{ $material->title }}</h1>
        <div class="mb-3 text-muted">
            Subject: {{ $material->subject?->name ?? '-' }} | 
            Kelas: {{ $material->subject?->schoolClass?->name ?? '-' }} |
            Pembuat: {{ $material->creator?->name ?? '-' }}
        </div>
        <div class="border rounded p-3 bg-light">
            {!! nl2br(e($material->content ?? '-')) !!}
        </div>
        <a href="{{ route('materials.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    </div>
</div>
@endsection
