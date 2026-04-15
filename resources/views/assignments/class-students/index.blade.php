@extends('layouts.app', ['title' => __('ui.assignments')])

@section('content')
<div x-data="classStudentAssignmentPage()" data-async-list data-fragment="#class-student-assignments-fragment">
<x-ui.page-header :title="__('ui.class_student_assignments_title')" :subtitle="__('ui.class_student_assignments_subtitle')">
    <x-slot:actions>
        <a href="{{ route('assignments.class-students.create') }}" class="tera-btn tera-btn-primary">
            <i data-lucide="plus" class="w-4 h-4"></i>{{ __('ui.assign_student') }}
        </a>
    </x-slot:actions>
</x-ui.page-header>

<x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_student_name_or_nis')">
    <x-slot:filters>
        <div>
            <label class="tera-label">{{ __('ui.classes') }}</label>
            <select name="class_id" class="tera-select">
                <option value="">{{ __('ui.all') }}</option>
                @foreach($classes as $klass)
                    <option value="{{ $klass->id }}" @selected((string)request('class_id') === (string)$klass->id)>{{ $klass->name }}</option>
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
            <label class="tera-label">{{ __('ui.grade_level') }}</label>
            <select name="grade_level" class="tera-select">
                <option value="">{{ __('ui.all') }}</option>
                @foreach([7,8,9] as $grade)
                    <option value="{{ $grade }}" @selected((string)request('grade_level') === (string)$grade)>{{ $grade }}</option>
                @endforeach
            </select>
        </div>
    </x-slot:filters>
</x-ui.table-toolbar>

<div id="class-student-assignments-fragment">
    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
                <tr>
                    <th>{{ __('ui.no') }}</th>
                    <th>{{ __('ui.student') }}</th>
                    <th>NIS</th>
                    <th>{{ __('ui.classes') }}</th>
                    <th>{{ __('ui.academic_year') }}</th>
                    <th>{{ __('ui.status') }}</th>
                    <th>{{ __('ui.action') }}</th>
                </tr>
            </thead>
            <tbody>
            @foreach($assignments as $a)
                <tr>
                    <td>{{ $assignments->firstItem() + $loop->index }}</td>
                    <td>{{ $a->student?->full_name }}</td>
                    <td>{{ $a->student?->nis }}</td>
                    <td>{{ $a->class?->name }}</td>
                    <td>{{ $a->academicYear?->name }}</td>
                    <td>
                        <span class="tera-badge tera-status-badge {{ $a->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-700' }}">{{ ucfirst($a->status) }}</span>
                    </td>
                    <td>
                        <div class="inline-flex items-center gap-2">
                            <a class="tera-btn tera-btn-muted !px-3 !py-1.5" href="{{ route('assignments.class-students.edit', $a) }}">{{ __('ui.edit') }}</a>
                            <button type="button" class="tera-btn tera-btn-danger !px-3 !py-1.5" @click="destroyItem({{ $a->id }}, @js($a->student?->full_name))">{{ __('ui.delete') }}</button>
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
function classStudentAssignmentPage() {
    return {
        async destroyItem(id, name) {
            const confirm = await window.Teramia.confirmDelete(
                @js(__('ui.delete_data_question')),
                `${@js(__('ui.delete'))} ${name}?`
            );
            if (!confirm.isConfirmed) return;

            try {
                const { response, payload } = await window.Teramia.fetchJson(`/assignments/class-students/${id}`, {
                    method: 'POST',
                    body: JSON.stringify({ _method: 'DELETE' }),
                });
                if (!response.ok) throw new Error(payload?.message || @js(__('ui.failed_delete_assignment')));
                await window.Teramia.toast('success', payload.message || @js(__('ui.assignment_deleted')));
                await window.Teramia.refreshFragment(window.location.href, '#class-student-assignments-fragment');
            } catch (error) {
                window.Teramia.toast('error', error.message || @js(__('ui.failed_delete_assignment')));
            }
        }
    };
}
</script>
@endsection

