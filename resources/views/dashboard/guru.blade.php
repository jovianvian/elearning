@extends('layouts.app', ['title' => 'Dashboard Guru'])

@section('content')
<div class="content-card">
    <h2>Dashboard Guru</h2>
    <p>Ringkasan aktivitas mengajar dan materi pembelajaran.</p>
    <div class="grid">
        <div class="metric"><div class="label">Mapel Diampu</div><div class="value">{{ $stats['mapel_diampu'] }}</div></div>
        <div class="metric"><div class="label">Materi Dibuat</div><div class="value">{{ $stats['materi_dibuat'] }}</div></div>
        <div class="metric"><div class="label">Kelas Wali/Libat</div><div class="value">{{ $stats['kelas_terlibat'] }}</div></div>
    </div>
</div>
@endsection
