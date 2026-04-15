<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppSettingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:150'],
            'school_name' => ['required', 'string', 'max:150'],
            'school_logo' => ['nullable', 'string', 'max:500'],
            'school_logo_file' => ['nullable', 'image', 'max:5120'],
            'school_favicon' => ['nullable', 'string', 'max:255'],
            'building_background' => ['nullable', 'string', 'max:500'],
            'building_background_file' => ['nullable', 'image', 'max:8192'],
            'primary_color' => ['required', 'string', 'max:20'],
            'secondary_color' => ['required', 'string', 'max:20'],
            'accent_color' => ['required', 'string', 'max:20'],
            'default_locale' => ['required', 'in:id,en'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'school_email' => ['nullable', 'email', 'max:255'],
            'school_phone' => ['nullable', 'string', 'max:50'],
            'school_address' => ['nullable', 'string'],
            'active_academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'active_semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
        ];
    }
}
