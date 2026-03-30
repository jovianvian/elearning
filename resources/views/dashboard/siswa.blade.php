@extends('layouts.app', ['title' => 'Dashboard Siswa'])

@section('content')
<h1 class="h4 mb-3">Dashboard Siswa</h1>
@if($schoolClass)
    <div class="alert alert-info">Kelas kamu: <strong>{{ $schoolClass }}</strong></div>
@endif
<div class="row g-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><small>Materi Tersedia</small><h3>{{ $stats['materi_tersedia'] }}</h3></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><small>Mapel Aktif</small><h3>{{ $stats['mapel_aktif'] }}</h3></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><small>Kelas Aktif</small><h3>{{ $stats['kelas_aktif'] }}</h3></div></div></div>
</div>
@endsection
