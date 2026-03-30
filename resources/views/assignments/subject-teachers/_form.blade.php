@csrf
<div class="grid md:grid-cols-2 gap-4">
<div><label class="block text-sm mb-1">Teacher</label><select name="teacher_id" class="w-full border rounded px-3 py-2">@foreach($teachers as $t)<option value="{{ $t->id }}" @selected(old('teacher_id', $subject_teacher->teacher_id ?? '')==$t->id)>{{ $t->full_name }} ({{ $t->nip }})</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Subject</label><select name="subject_id" class="w-full border rounded px-3 py-2">@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(old('subject_id', $subject_teacher->subject_id ?? '')==$s->id)>{{ $s->name_id }}</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Academic Year</label><select name="academic_year_id" class="w-full border rounded px-3 py-2">@foreach($years as $y)<option value="{{ $y->id }}" @selected(old('academic_year_id', $subject_teacher->academic_year_id ?? '')==$y->id)>{{ $y->name }}</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Active</label><select name="is_active" class="w-full border rounded px-3 py-2"><option value="1" @selected(old('is_active', $subject_teacher->is_active ?? true))>Yes</option><option value="0" @selected(!old('is_active', $subject_teacher->is_active ?? true))>No</option></select></div>
</div>
