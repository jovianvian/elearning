@extends('layouts.app', ['title' => 'Dashboard Guru'])

@section('content')
<h1 class="h4 mb-3">Dashboard Guru</h1>
<div class="row g-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><small>Mapel Diampu</small><h3>{{ $stats['mapel_diampu'] }}</h3></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><small>Materi Dibuat</small><h3>{{ $stats['materi_dibuat'] }}</h3></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><small>Kelas Terlibat</small><h3>{{ $stats['kelas_terlibat'] }}</h3></div></div></div>
</div>
@endsection
