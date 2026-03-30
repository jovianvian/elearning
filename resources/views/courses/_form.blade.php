@csrf
<div class="grid md:grid-cols-2 gap-4">
<div><label class="block text-sm mb-1">Title</label><input name="title" class="w-full border rounded px-3 py-2" value="{{ old('title', $course->title ?? '') }}" required></div>
<div><label class="block text-sm mb-1">Subject</label><select name="subject_id" class="w-full border rounded px-3 py-2" required>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id', $course->subject_id ?? '')==$subject->id)>{{ $subject->name_id }}</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Class</label><select name="class_id" class="w-full border rounded px-3 py-2" required>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(old('class_id', $course->class_id ?? '')==$class->id)>{{ $class->name }}</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Academic Year</label><select name="academic_year_id" class="w-full border rounded px-3 py-2" required>@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected(old('academic_year_id', $course->academic_year_id ?? '')==$year->id)>{{ $year->name }}</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Semester</label><select name="semester_id" class="w-full border rounded px-3 py-2" required>@foreach($semesters as $semester)<option value="{{ $semester->id }}" @selected(old('semester_id', $course->semester_id ?? '')==$semester->id)>{{ $semester->name }} ({{ $semester->code }})</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Published</label><select name="is_published" class="w-full border rounded px-3 py-2"><option value="0" @selected(!old('is_published', $course->is_published ?? false))>No</option><option value="1" @selected(old('is_published', $course->is_published ?? false))>Yes</option></select></div>
<div class="md:col-span-2"><label class="block text-sm mb-1">Teachers (must match subject assignment)</label><select name="teacher_ids[]" class="w-full border rounded px-3 py-2" multiple required>
@php($selectedTeachers = old('teacher_ids', isset($course) ? $course->teachers->pluck('id')->all() : []))
@foreach($teachers as $teacher)
<option value="{{ $teacher->id }}" @selected(in_array($teacher->id, $selectedTeachers))>{{ $teacher->full_name }} ({{ $teacher->nip ?? $teacher->username }})</option>
@endforeach
</select><p class="text-xs text-slate-500 mt-1">Use Ctrl/Cmd + click to select multiple.</p></div>
<div class="md:col-span-2"><label class="block text-sm mb-1">Main Teacher (optional)</label><select name="main_teacher_id" class="w-full border rounded px-3 py-2"><option value="">-</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(old('main_teacher_id', isset($course) ? optional($course->teachers->firstWhere('pivot.is_main_teacher', true))->id : '')==$teacher->id)>{{ $teacher->full_name }}</option>@endforeach</select></div>
<div class="md:col-span-2"><label class="block text-sm mb-1">Description</label><textarea name="description" class="w-full border rounded px-3 py-2" rows="4">{{ old('description', $course->description ?? '') }}</textarea></div>
</div>
