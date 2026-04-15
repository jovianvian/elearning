@extends('layouts.app', ['title' => __('ui.learning_materials')])

@section('content')
<div x-data data-async-list data-fragment="#student-materials-fragment">
    <x-ui.page-header :title="__('ui.my_learning_materials_title')" :subtitle="__('ui.my_learning_materials_subtitle')" />

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_material_or_course')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.course') }}</label>
                <select name="course_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected((string) request('course_id') === (string) $course->id)>
                            {{ $course->title }}
                        </option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div id="student-materials-fragment">
        <div class="tera-table-wrap">
            <table class="tera-table">
                <thead>
                <tr>
                    <th>{{ __('ui.no') }}</th>
                    <th>{{ __('ui.title') }}</th>
                    <th>{{ __('ui.course') }}</th>
                    <th>{{ __('ui.material_type') }}</th>
                    <th>{{ __('ui.progress') }}</th>
                    <th>{{ __('ui.last_accessed') }}</th>
                    <th>{{ __('ui.action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($materials as $material)
                    @php($progress = $material->progresses->first())
                    <tr>
                        <td>{{ $materials->firstItem() + $loop->index }}</td>
                        <td class="!text-left">
                            <div class="font-semibold text-slate-800">{{ $material->title }}</div>
                            <div class="text-xs text-slate-500">{{ $material->description ? \Illuminate\Support\Str::limit($material->description, 70) : '-' }}</div>
                        </td>
                        <td>{{ $material->course?->title ?? '-' }}</td>
                        <td><span class="tera-badge bg-slate-100 text-slate-700">{{ __('ui.material_type_'.$material->type) }}</span></td>
                        <td>
                            @php($status = $progress->status ?? \App\Models\StudentMaterialProgress::STATUS_NOT_STARTED)
                            <span class="tera-badge
                                {{ $status === \App\Models\StudentMaterialProgress::STATUS_COMPLETED ? 'bg-emerald-100 text-emerald-700' : ($status === \App\Models\StudentMaterialProgress::STATUS_IN_PROGRESS ? 'bg-sky-100 text-sky-700' : 'bg-slate-200 text-slate-700') }}">
                                {{ __('ui.progress_'.$status) }}
                            </span>
                        </td>
                        <td>{{ optional($progress?->last_accessed_at)->format('d M Y H:i') ?: '-' }}</td>
                        <td>
                            <a href="{{ route('student-materials.show', $material) }}" class="tera-btn tera-btn-primary !px-3 !py-1.5">{{ __('ui.open') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-slate-500">{{ __('ui.no_learning_materials_available') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $materials->links() }}</div>
    </div>
</div>
@endsection
