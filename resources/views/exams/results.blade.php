@extends('layouts.app', ['title' => 'Hasil Ujian'])

@section('content')
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6">
        <h2 class="text-xl font-semibold">Hasil Ujian</h2>
        <p class="text-sm text-slate-500">{{ $exam->title }} - {{ $exam->course?->title }}</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 mobile-table-scroll">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="p-3 text-left">Siswa</th>
                <th class="p-3 text-center">Status</th>
                <th class="p-3 text-center">Objektif</th>
                <th class="p-3 text-center">Esai</th>
                <th class="p-3 text-center">Nilai Akhir</th>
                <th class="p-3 text-center">Dipublikasi</th>
                <th class="p-3 text-center">Mencurigakan</th>
            </tr>
            </thead>
            <tbody>
            @forelse($attempts as $attempt)
                <tr class="border-t border-slate-100">
                    <td class="p-3">{{ $attempt->student?->full_name }}</td>
                    <td class="p-3 text-center">{{ $attempt->status }}</td>
                    <td class="p-3 text-center">{{ $attempt->score_objective }}</td>
                    <td class="p-3 text-center">{{ $attempt->score_essay }}</td>
                    <td class="p-3 text-center font-semibold">{{ $attempt->final_score }}</td>
                    <td class="p-3 text-center">{{ $attempt->is_published ? __('ui.yes') : __('ui.no') }}</td>
                    <td class="p-3 text-center">{{ $attempt->suspicious_flag ? __('ui.yes') : __('ui.no') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-6 text-center text-slate-500">Belum ada data hasil.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{ $attempts->links() }}
@endsection
