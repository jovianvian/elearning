@extends('layouts.app', ['title' => 'Dashboard Admin'])

@section('content')
<div class="content-card">
    <h2>Dashboard Admin</h2>
    <p>Pusat kontrol awal untuk monitoring data pengguna dan konten belajar.</p>
    <div class="grid">
        <div class="metric"><div class="label">Total User</div><div class="value">{{ $stats['total_users'] }}</div></div>
        <div class="metric"><div class="label">Total Guru</div><div class="value">{{ $stats['total_guru'] }}</div></div>
        <div class="metric"><div class="label">Total Siswa</div><div class="value">{{ $stats['total_siswa'] }}</div></div>
        <div class="metric"><div class="label">Total Kelas</div><div class="value">{{ $stats['total_kelas'] }}</div></div>
        <div class="metric"><div class="label">Total Mata Pelajaran</div><div class="value">{{ $stats['total_mapel'] }}</div></div>
        <div class="metric"><div class="label">Total Materi</div><div class="value">{{ $stats['total_materi'] }}</div></div>
    </div>
</div>
@endsection
