@csrf
<div class="grid md:grid-cols-2 gap-4">
    <div><label class="tera-label">Class Name</label><input name="name" class="tera-input" value="{{ old('name', $schoolClass->name ?? '') }}" required></div>
    <div><label class="tera-label">Code</label><input name="code" class="tera-input" value="{{ old('code', $schoolClass->code ?? '') }}"></div>
    <div><label class="tera-label">Grade Level</label><select name="grade_level" class="tera-select">@foreach([7,8,9] as $g)<option value="{{ $g }}" @selected(old('grade_level', $schoolClass->grade_level ?? '')==$g)>{{ $g }}</option>@endforeach</select></div>
    <div><label class="tera-label">Academic Year</label><select name="academic_year_id" class="tera-select">@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected(old('academic_year_id', $schoolClass->academic_year_id ?? '')==$year->id)>{{ $year->name }}</option>@endforeach</select></div>
    <div><label class="tera-label">Homeroom Teacher</label><select name="homeroom_teacher_id" class="tera-select"><option value="">-</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(old('homeroom_teacher_id', $schoolClass->homeroom_teacher_id ?? '')==$teacher->id)>{{ $teacher->full_name }}</option>@endforeach</select></div>
    <div><label class="tera-label">Active</label><select name="is_active" class="tera-select"><option value="1" @selected(old('is_active', $schoolClass->is_active ?? true))>Yes</option><option value="0" @selected(!old('is_active', $schoolClass->is_active ?? true))>No</option></select></div>
</div>
