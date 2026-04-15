@extends('layouts.app', ['title' => __('ui.e_rapor')])

@section('content')
    <x-ui.page-header :title="__('ui.e_rapor_title')" :subtitle="__('ui.e_rapor_subtitle')" />

    <div class="grid gap-3 md:grid-cols-3">
        <div class="tera-card">
            <div class="tera-card-body">
                <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('ui.students') }}</div>
                <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($totalStudents) }}</div>
            </div>
        </div>
        <div class="tera-card">
            <div class="tera-card-body">
                <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('ui.total_attempts') }}</div>
                <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($totalAttempts) }}</div>
            </div>
        </div>
        <div class="tera-card">
            <div class="tera-card-body">
                <div class="text-xs uppercase tracking-wide text-slate-500">{{ __('ui.average_score') }}</div>
                <div class="mt-1 text-2xl font-extrabold text-slate-900">{{ number_format($averageScore, 2) }}</div>
            </div>
        </div>
    </div>

    <x-ui.table-toolbar :search-value="$search" :search-placeholder="__('ui.search_student_name_or_nis')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.classes') }}</label>
                <select name="class_id" class="tera-select">
                    <option value="">{{ __('ui.all_classes') }}</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected($selectedClassId === (int) $class->id)>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
            <tr>
                <th>{{ __('ui.no') }}</th>
                <th>{{ __('ui.full_name') }}</th>
                <th>NIS</th>
                <th>{{ __('ui.class_name') }}</th>
                <th>{{ __('ui.exam_attempts') }}</th>
                <th>{{ __('ui.average_score') }}</th>
                <th>{{ __('ui.date') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ ($rows->currentPage() - 1) * $rows->perPage() + $loop->iteration }}</td>
                    <td class="font-medium text-slate-900">{{ $row->full_name }}</td>
                    <td>{{ $row->nis ?: '-' }}</td>
                    <td>{{ $row->class_name }}</td>
                    <td>{{ $row->exam_count }}</td>
                    <td class="font-semibold">{{ number_format((float) $row->avg_score, 2) }}</td>
                    <td>{{ $row->last_submitted_at ? \Illuminate\Support\Carbon::parse($row->last_submitted_at)->format('d M Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-slate-500">{{ __('ui.no_data') }}</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rows->links() }}
    </div>
@endsection

