@extends('layouts.app', ['title' => __('ui.assignments')])

@section('content')
<div x-data="subjectTeacherAssignmentPage()" data-async-list data-fragment="#subject-teacher-assignments-fragment">
<x-ui.page-header :title="__('ui.subject_teacher_assignments_title')" :subtitle="__('ui.subject_teacher_assignments_subtitle')">
    <x-slot:actions>
        <a href="{{ route('assignments.subject-teachers.create') }}" class="tera-btn tera-btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.assign_teacher') }}
        </a>
    </x-slot:actions>
</x-ui.page-header>

<x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_teacher_nip_or_subject')">
    <x-slot:filters>
        <div>
            <label class="tera-label">{{ __('ui.subjects') }}</label>
            <select name="subject_id" class="tera-select">
                <option value="">{{ __('ui.all') }}</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string)request('subject_id') === (string)$subject->id)>{{ $subject->name_id }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="tera-label">{{ __('ui.academic_year') }}</label>
            <select name="academic_year_id" class="tera-select">
                <option value="">{{ __('ui.all') }}</option>
                @foreach($years as $year)
                    <option value="{{ $year->id }}" @selected((string)request('academic_year_id') === (string)$year->id)>{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="tera-label">{{ __('ui.active') }}</label>
            <select name="is_active" class="tera-select">
                <option value="">{{ __('ui.all') }}</option>
                <option value="1" @selected(request('is_active') === '1')>{{ __('ui.active') }}</option>
                <option value="0" @selected(request('is_active') === '0')>{{ __('ui.inactive') }}</option>
            </select>
        </div>
    </x-slot:filters>
</x-ui.table-toolbar>

<div id="subject-teacher-assignments-fragment">
    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
                <tr>
                    <th>{{ __('ui.no') }}</th>
                    <th>{{ __('ui.teacher') }}</th>
                    <th>NIP</th>
                    <th>{{ __('ui.subjects') }}</th>
                    <th>{{ __('ui.academic_year') }}</th>
                    <th>{{ __('ui.active') }}</th>
                    <th>{{ __('ui.action') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($assignments as $a)
                <tr>
                    <td>{{ $assignments->firstItem() + $loop->index }}</td>
                    <td>{{ $a->teacher?->full_name }}</td>
                    <td>{{ $a->teacher?->nip }}</td>
                    <td>{{ $a->subject?->name_id }}</td>
                    <td>{{ $a->academicYear?->name }}</td>
                    <td>
                        <span class="tera-badge tera-status-badge {{ $a->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ $a->is_active ? __('ui.active') : __('ui.inactive') }}</span>
                    </td>
                    <td>
                        <div class="inline-flex items-center gap-2">
                            <a class="tera-btn tera-btn-muted !px-3 !py-1.5" href="{{ route('assignments.subject-teachers.edit', $a) }}">{{ __('ui.edit') }}</a>
                            <button type="button" class="tera-btn tera-btn-danger !px-3 !py-1.5" @click="destroyItem({{ $a->id }}, @js($a->teacher?->full_name))">{{ __('ui.delete') }}</button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $assignments->links() }}</div>
</div>
</div>

<script>
function subjectTeacherAssignmentPage() {
    return {
        async destroyItem(id, name) {
            const confirm = await window.Teramia.confirmDelete(
                @js(__('ui.delete_data_question')),
                `${@js(__('ui.delete'))} ${name}?`
            );
            if (!confirm.isConfirmed) return;

            try {
                const { response, payload } = await window.Teramia.fetchJson(`/assignments/subject-teachers/${id}`, {
                    method: 'POST',
                    body: JSON.stringify({ _method: 'DELETE' }),
                });
                if (!response.ok) throw new Error(payload?.message || @js(__('ui.failed_delete_assignment')));
                await window.Teramia.toast('success', payload.message || @js(__('ui.assignment_deleted')));
                await window.Teramia.refreshFragment(window.location.href, '#subject-teacher-assignments-fragment');
            } catch (error) {
                window.Teramia.toast('error', error.message || @js(__('ui.failed_delete_assignment')));
            }
        }
    };
}
</script>
@endsection

