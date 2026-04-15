@extends('layouts.app', ['title' => __('ui.exams')])

@section('content')
<div x-data="examListPage()" data-async-list data-fragment="#exams-table-fragment">
    <x-ui.page-header :title="__('ui.exam_management_title')" :subtitle="__('ui.exam_management_subtitle')">
        <x-slot:actions>
            @if(auth()->user()->hasRole('super_admin','admin','teacher'))
                <a href="{{ route('exams.create') }}" class="tera-btn tera-btn-primary">{{ __('ui.create_exam') }}</a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table-toolbar :search-value="request('q')" :search-placeholder="__('ui.search_exam_or_course')">
        <x-slot:filters>
            <div>
                <label class="tera-label">{{ __('ui.course') }}</label>
                <select name="course_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected((string)request('course_id') === (string)$course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
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
                <label class="tera-label">{{ __('ui.classes') }}</label>
                <select name="class_id" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($classes as $klass)
                        <option value="{{ $klass->id }}" @selected((string)request('class_id') === (string)$klass->id)>{{ $klass->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.status') }}</label>
                <select name="status" class="tera-select">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach(['draft','scheduled','active','closed','graded','archived'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('ui.status_'.$status) }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div id="exams-table-fragment">
        <div class="tera-table-wrap">
            <table class="tera-table">
                <thead>
                <tr>
                    <th>{{ __('ui.no') }}</th>
                    <th>{{ __('ui.title') }}</th>
                    <th>{{ __('ui.course') }}</th>
                    <th>{{ __('ui.window') }}</th>
                    <th>{{ __('ui.attempts') }}</th>
                    <th>{{ __('ui.status') }}</th>
                    <th>{{ __('ui.published') }}</th>
                    <th>{{ __('ui.action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($exams as $exam)
                    <tr>
                        <td>{{ $exams->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="font-medium">{{ $exam->title }}</div>
                            <div class="text-xs text-slate-500">
                                @switch($exam->exam_type)
                                    @case('objective_single_choice')
                                        Objective - Single Choice
                                        @break
                                    @case('objective_multi_response')
                                        Objective - Multi Response
                                        @break
                                    @case('objective_short_answer')
                                        Objective - Short Answer
                                        @break
                                    @default
                                        {{ ucfirst(str_replace('_', ' ', $exam->exam_type)) }}
                                @endswitch
                                | {{ $exam->duration_minutes }} {{ __('ui.minutes_short') }}
                            </div>
                        </td>
                        <td>
                            <div>{{ $exam->course?->title }}</div>
                            <div class="text-xs text-slate-500">{{ $exam->course?->subject?->name_id }} - {{ $exam->course?->schoolClass?->name }}</div>
                        </td>
                        <td class="text-xs">
                            <div>{{ $exam->start_at?->format('d M Y H:i') }}</div>
                            <div>{{ $exam->end_at?->format('d M Y H:i') }}</div>
                        </td>
                        <td class="text-center">{{ $exam->max_attempts }}</td>
                        <td class="text-center">
                            <span class="tera-badge tera-status-badge bg-skyx/20 text-sky-700">{{ __('ui.status_'.$exam->effective_status) }}</span>
                        </td>
                        <td class="text-center">{{ $exam->is_published ? __('ui.yes') : __('ui.no') }}</td>
                        <td>
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('exams.show', $exam) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">{{ __('ui.detail') }}</a>
                                @if(auth()->user()->hasRole('super_admin','admin','teacher'))
                                    <a href="{{ route('exams.edit', $exam) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">{{ __('ui.edit') }}</a>
                                    <button type="button" class="tera-btn tera-btn-danger !px-3 !py-1.5" @click="destroyExam({{ $exam->id }}, @js($exam->title))">{{ __('ui.delete') }}</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-500 py-8">{{ __('ui.no_exams_available') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $exams->links() }}</div>
    </div>
</div>

<script>
function examListPage() {
    return {
        async destroyExam(id, title) {
            const confirm = await window.Teramia.confirmDelete(
                @js(__('ui.delete_data_question')),
                @js(__('ui.delete_exam_prompt')) + ' ' + title + '?'
            );
            if (!confirm.isConfirmed) return;

            try {
                const { response, payload } = await window.Teramia.fetchJson(`/exams/${id}`, {
                    method: 'POST',
                    body: JSON.stringify({ _method: 'DELETE' }),
                });
                if (!response.ok) throw new Error(payload?.message || @js(__('ui.failed_delete_exam')));
                await window.Teramia.toast('success', payload.message || @js(__('ui.exam_moved_to_trash')));
                await window.Teramia.refreshFragment(window.location.href, '#exams-table-fragment');
            } catch (error) {
                window.Teramia.toast('error', error.message || @js(__('ui.failed_delete_exam')));
            }
        }
    };
}
</script>
@endsection
