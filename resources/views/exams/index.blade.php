@extends('layouts.app', ['title' => 'Exams'])

@section('content')
    <x-ui.page-header title="Exam Management" subtitle="Create, schedule, and manage online exams across courses.">
        <x-slot:actions>
            @if(auth()->user()->hasRole('super_admin','admin','teacher'))
                <a href="{{ route('exams.create') }}" class="tera-btn tera-btn-primary">Create Exam</a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.table-toolbar :search-value="request('q')" search-placeholder="Search exam or course">
        <x-slot:filters>
            <div>
                <label class="tera-label">Course</label>
                <select name="course_id" class="tera-select">
                    <option value="">All</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected((string)request('course_id') === (string)$course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.subjects') }}</label>
                <select name="subject_id" class="tera-select">
                    <option value="">All</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string)request('subject_id') === (string)$subject->id)>{{ $subject->name_id }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.classes') }}</label>
                <select name="class_id" class="tera-select">
                    <option value="">All</option>
                    @foreach($classes as $klass)
                        <option value="{{ $klass->id }}" @selected((string)request('class_id') === (string)$klass->id)>{{ $klass->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="tera-label">{{ __('ui.status') }}</label>
                <select name="status" class="tera-select">
                    <option value="">All</option>
                    @foreach(['draft','scheduled','active','closed','graded','archived'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-ui.table-toolbar>

    <div class="tera-table-wrap">
        <table class="tera-table">
            <thead>
            <tr>
                <th>No</th>
                <th>Title</th>
                <th>Course</th>
                <th>Window</th>
                <th>Attempts</th>
                <th>Status</th>
                <th>Published</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($exams as $exam)
                <tr>
                    <td>{{ $exams->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="font-medium">{{ $exam->title }}</div>
                        <div class="text-xs text-slate-500">{{ $exam->exam_type }} | {{ $exam->duration_minutes }} min</div>
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
                        <span class="tera-badge bg-skyx/20 text-sky-700">{{ $exam->effective_status }}</span>
                    </td>
                    <td class="text-center">{{ $exam->is_published ? 'Yes' : 'No' }}</td>
                    <td>
                        <div class="inline-flex items-center gap-2">
                            <a href="{{ route('exams.show', $exam) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">Detail</a>
                            @if(auth()->user()->hasRole('super_admin','admin','teacher'))
                                <a href="{{ route('exams.edit', $exam) }}" class="tera-btn tera-btn-muted !px-3 !py-1.5">Edit</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-slate-500 py-8">No exams available.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $exams->links() }}</div>
@endsection
