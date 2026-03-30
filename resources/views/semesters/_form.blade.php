@csrf
<div class="grid md:grid-cols-2 gap-4">
<div><label class="block text-sm mb-1">Academic Year</label><select name="academic_year_id" class="w-full border rounded px-3 py-2" required>@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected(old('academic_year_id', $semester->academic_year_id ?? '') == $year->id)>{{ $year->name }}</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Name</label><input name="name" class="w-full border rounded px-3 py-2" value="{{ old('name', $semester->name ?? '') }}" required></div>
<div><label class="block text-sm mb-1">Code</label><input name="code" class="w-full border rounded px-3 py-2" value="{{ old('code', $semester->code ?? '') }}" required></div>
<div><label class="block text-sm mb-1">Active</label><select name="is_active" class="w-full border rounded px-3 py-2"><option value="0">No</option><option value="1" @selected(old('is_active', $semester->is_active ?? false))>Yes</option></select></div>
<div><label class="block text-sm mb-1">Start Date</label><input type="date" name="start_date" class="w-full border rounded px-3 py-2" value="{{ old('start_date', isset($semester) && $semester->start_date ? $semester->start_date->format('Y-m-d') : '') }}"></div>
<div><label class="block text-sm mb-1">End Date</label><input type="date" name="end_date" class="w-full border rounded px-3 py-2" value="{{ old('end_date', isset($semester) && $semester->end_date ? $semester->end_date->format('Y-m-d') : '') }}"></div>
</div>
