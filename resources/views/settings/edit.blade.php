@extends('layouts.app', ['title' => 'Settings'])

@section('content')
<x-ui.page-header title="Application Settings" subtitle="Manage branding, locale, school information, and active academic period." />

<form method="POST" action="{{ route('super-admin.settings.update') }}" class="tera-card" x-data="{}" autocomplete="off">
    @csrf
    @method('PUT')

    <div class="tera-card-body space-y-6">
        <div>
            <h3 class="text-sm font-bold text-slate-800 mb-3">{{ __('ui.branding') }}</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="tera-label">{{ __('ui.app_name') }}</label>
                    <input name="app_name" class="tera-input" value="{{ old('app_name', $setting->app_name) }}" required autocapitalize="off" spellcheck="false">
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.school_name') }}</label>
                    <input name="school_name" class="tera-input" value="{{ old('school_name', $setting->school_name) }}" required>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.default_locale') }}</label>
                    <select name="default_locale" class="tera-select">
                        <option value="id" @selected(old('default_locale', $setting->default_locale)=='id')>Indonesian</option>
                        <option value="en" @selected(old('default_locale', $setting->default_locale)=='en')>English</option>
                    </select>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-bold text-slate-800 mb-3">{{ __('ui.theme_colors') }}</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="tera-label">{{ __('ui.primary_color') }}</label>
                    <input name="primary_color" class="tera-input" value="{{ old('primary_color', $setting->primary_color) }}" spellcheck="false">
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.secondary_color') }}</label>
                    <input name="secondary_color" class="tera-input" value="{{ old('secondary_color', $setting->secondary_color) }}" spellcheck="false">
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.accent_color') }}</label>
                    <input name="accent_color" class="tera-input" value="{{ old('accent_color', $setting->accent_color) }}" spellcheck="false">
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-bold text-slate-800 mb-3">{{ __('ui.school_contact') }}</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="tera-label">{{ __('ui.school_email') }}</label>
                    <input name="school_email" class="tera-input" value="{{ old('school_email', $setting->school_email) }}">
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.school_phone') }}</label>
                    <input name="school_phone" class="tera-input" value="{{ old('school_phone', $setting->school_phone) }}">
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.footer_text') }}</label>
                    <input name="footer_text" class="tera-input" value="{{ old('footer_text', $setting->footer_text) }}">
                </div>
                <div class="md:col-span-3">
                    <label class="tera-label">{{ __('ui.school_address') }}</label>
                    <textarea name="school_address" class="tera-textarea" rows="3">{{ old('school_address', $setting->school_address) }}</textarea>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-bold text-slate-800 mb-3">{{ __('ui.active_academic_period') }}</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="tera-label">{{ __('ui.active_academic_year') }}</label>
                    <select name="active_academic_year_id" class="tera-select">
                        <option value="">-</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected(old('active_academic_year_id', $setting->active_academic_year_id)==$year->id)>{{ $year->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="tera-label">{{ __('ui.semester') }}</label>
                    <select name="active_semester_id" class="tera-select">
                        <option value="">-</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" @selected(old('active_semester_id', $setting->active_semester_id)==$semester->id)>{{ $semester->name }} ({{ $semester->code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 flex justify-end gap-2">
            <button type="submit" class="tera-btn tera-btn-primary">{{ __('ui.save_settings') }}</button>
        </div>
    </div>
</form>
@endsection


