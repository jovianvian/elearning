@extends('layouts.app', ['title' => 'Materi Pembelajaran'])

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Daftar Materi</h1>
    @if(auth()->user()->hasRole('admin','guru'))
        <a href="{{ route('materials.create') }}" class="btn btn-primary">Tambah Materi</a>
    @endif
</div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead><tr><th>Judul</th><th>Subject</th><th>Kelas</th><th>Pembuat</th><th>Publikasi</th><th width="190">Aksi</th></tr></thead>
            <tbody>
            @forelse($materials as $material)
                <tr>
                    <td>{{ $material->title }}</td>
                    <td>{{ $material->subject?->name ?? '-' }}</td>
                    <td>{{ $material->subject?->schoolClass?->name ?? '-' }}</td>
                    <td>{{ $material->creator?->name ?? '-' }}</td>
                    <td>{{ $material->published_at ? $material->published_at->format('d M Y H:i') : '-' }}</td>
                    <td>
                        <a class="btn btn-sm btn-info" href="{{ route('materials.show', $material) }}">Detail</a>
                        @if(auth()->user()->hasRole('admin') || (auth()->user()->hasRole('guru') && $material->created_by === auth()->id()))
                            <a class="btn btn-sm btn-warning" href="{{ route('materials.edit', $material) }}">Edit</a>
                            <form method="POST" action="{{ route('materials.destroy', $material) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus materi ini?')">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-3">Belum ada materi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $materials->links() }}</div>
@endsection
