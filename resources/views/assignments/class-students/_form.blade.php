@csrf
<div class="grid md:grid-cols-2 gap-4">
<div><label class="block text-sm mb-1">Student</label><select name="student_id" class="w-full border rounded px-3 py-2">@foreach($students as $s)<option value="{{ $s->id }}" @selected(old('student_id', $class_student->student_id ?? '')==$s->id)>{{ $s->full_name }} ({{ $s->nis }})</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Class</label><select name="class_id" class="w-full border rounded px-3 py-2">@foreach($classes as $c)<option value="{{ $c->id }}" @selected(old('class_id', $class_student->class_id ?? '')==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Academic Year</label><select name="academic_year_id" class="w-full border rounded px-3 py-2">@foreach($years as $y)<option value="{{ $y->id }}" @selected(old('academic_year_id', $class_student->academic_year_id ?? '')==$y->id)>{{ $y->name }}</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Status</label><select name="status" class="w-full border rounded px-3 py-2">@foreach(['active','inactive','moved'] as $st)<option value="{{ $st }}" @selected(old('status', $class_student->status ?? 'active')==$st)>{{ $st }}</option>@endforeach</select></div>
</div>
