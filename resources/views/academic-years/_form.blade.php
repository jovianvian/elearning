@csrf
<div class="grid md:grid-cols-2 gap-4">
<div><label class="block text-sm mb-1">Name</label><input name="name" class="w-full border rounded px-3 py-2" value="{{ old('name', $academicYear->name ?? '') }}" required></div>
<div><label class="block text-sm mb-1">Is Active</label><select name="is_active" class="w-full border rounded px-3 py-2"><option value="0">No</option><option value="1" @selected(old('is_active', $academicYear->is_active ?? false))>Yes</option></select></div>
<div><label class="block text-sm mb-1">Start Date</label><input type="date" name="start_date" class="w-full border rounded px-3 py-2" value="{{ old('start_date', isset($academicYear) ? $academicYear->start_date?->format('Y-m-d') : '') }}" required></div>
<div><label class="block text-sm mb-1">End Date</label><input type="date" name="end_date" class="w-full border rounded px-3 py-2" value="{{ old('end_date', isset($academicYear) ? $academicYear->end_date?->format('Y-m-d') : '') }}" required></div>
</div>
