@extends('layouts.app', ['title' => 'App Settings'])
@section('content')
<form method="POST" action="{{ route('super-admin.settings.update') }}" class="bg-white p-4 rounded shadow space-y-4">
@csrf
@method('PUT')
<div class="grid md:grid-cols-3 gap-4">
<div><label class="block text-sm mb-1">App Name</label><input name="app_name" class="w-full border rounded px-3 py-2" value="{{ old('app_name', $setting->app_name) }}" required></div>
<div><label class="block text-sm mb-1">School Name</label><input name="school_name" class="w-full border rounded px-3 py-2" value="{{ old('school_name', $setting->school_name) }}" required></div>
<div><label class="block text-sm mb-1">Default Locale</label><select name="default_locale" class="w-full border rounded px-3 py-2"><option value="id" @selected(old('default_locale', $setting->default_locale)=='id')>Indonesian</option><option value="en" @selected(old('default_locale', $setting->default_locale)=='en')>English</option></select></div>
<div><label class="block text-sm mb-1">Primary Color</label><input name="primary_color" class="w-full border rounded px-3 py-2" value="{{ old('primary_color', $setting->primary_color) }}"></div>
<div><label class="block text-sm mb-1">Secondary Color</label><input name="secondary_color" class="w-full border rounded px-3 py-2" value="{{ old('secondary_color', $setting->secondary_color) }}"></div>
<div><label class="block text-sm mb-1">Accent Color</label><input name="accent_color" class="w-full border rounded px-3 py-2" value="{{ old('accent_color', $setting->accent_color) }}"></div>
<div><label class="block text-sm mb-1">School Email</label><input name="school_email" class="w-full border rounded px-3 py-2" value="{{ old('school_email', $setting->school_email) }}"></div>
<div><label class="block text-sm mb-1">School Phone</label><input name="school_phone" class="w-full border rounded px-3 py-2" value="{{ old('school_phone', $setting->school_phone) }}"></div>
<div><label class="block text-sm mb-1">Footer Text</label><input name="footer_text" class="w-full border rounded px-3 py-2" value="{{ old('footer_text', $setting->footer_text) }}"></div>
<div><label class="block text-sm mb-1">Active Academic Year</label><select name="active_academic_year_id" class="w-full border rounded px-3 py-2"><option value="">-</option>@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected(old('active_academic_year_id', $setting->active_academic_year_id)==$year->id)>{{ $year->name }}</option>@endforeach</select></div>
<div><label class="block text-sm mb-1">Active Semester</label><select name="active_semester_id" class="w-full border rounded px-3 py-2"><option value="">-</option>@foreach($semesters as $semester)<option value="{{ $semester->id }}" @selected(old('active_semester_id', $setting->active_semester_id)==$semester->id)>{{ $semester->name }} ({{ $semester->code }})</option>@endforeach</select></div>
<div class="md:col-span-3"><label class="block text-sm mb-1">School Address</label><textarea name="school_address" class="w-full border rounded px-3 py-2" rows="3">{{ old('school_address', $setting->school_address) }}</textarea></div>
</div>
<button class="px-3 py-2 bg-primary text-white rounded">Save Settings</button>
</form>
@endsection
