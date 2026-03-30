@csrf
<div class="grid md:grid-cols-2 gap-4">
<div><label class="block text-sm mb-1">Name (Indonesian)</label><input name="name_id" class="w-full border rounded px-3 py-2" value="{{ old('name_id', $subject->name_id ?? '') }}" required></div>
<div><label class="block text-sm mb-1">Name (English)</label><input name="name_en" class="w-full border rounded px-3 py-2" value="{{ old('name_en', $subject->name_en ?? '') }}"></div>
<div><label class="block text-sm mb-1">Code</label><input name="code" class="w-full border rounded px-3 py-2" value="{{ old('code', $subject->code ?? '') }}"></div>
<div><label class="block text-sm mb-1">Active</label><select name="is_active" class="w-full border rounded px-3 py-2"><option value="1" @selected(old('is_active', $subject->is_active ?? true))>Yes</option><option value="0" @selected(!old('is_active', $subject->is_active ?? true))>No</option></select></div>
<div class="md:col-span-2"><label class="block text-sm mb-1">Description</label><textarea name="description" class="w-full border rounded px-3 py-2" rows="4">{{ old('description', $subject->description ?? '') }}</textarea></div>
</div>
