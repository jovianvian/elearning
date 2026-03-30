@extends('layouts.app', ['title' => 'Dashboard Siswa'])

@section('content')
<div class="content-card">
    <h2>Dashboard Siswa</h2>
    <p>Ringkasan materi yang siap dipelajari di kelas aktif.</p>
    <div class="grid">
        <div class="metric"><div class="label">Materi Tersedia</div><div class="value">{{ $stats['materi_tersedia'] }}</div></div>
        <div class="metric"><div class="label">Mata Pelajaran Aktif</div><div class="value">{{ $stats['mapel_aktif'] }}</div></div>
        <div class="metric"><div class="label">Kelas Aktif</div><div class="value">{{ $stats['kelas_aktif'] }}</div></div>
    </div>
</div>
@endsection
