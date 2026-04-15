<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAppSettingRequest;
use App\Models\AcademicYear;
use App\Models\AppSetting;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class AppSettingController extends Controller
{
    public function edit(): View
    {
        $setting = AppSetting::firstOrCreate(['id' => 1], [
            'app_name' => 'Teramia E-Learning',
            'school_name' => 'SMP Teramia',
            'default_locale' => 'id',
            'supported_locales_json' => ['id', 'en'],
        ]);

        $academicYears = AcademicYear::orderByDesc('is_active')->orderByDesc('id')->get();
        $semesters = Semester::orderByDesc('is_active')->orderByDesc('id')->get();

        return view('settings.edit', compact('setting', 'academicYears', 'semesters'));
    }

    public function update(UpdateAppSettingRequest $request): RedirectResponse
    {
        $setting = AppSetting::firstOrCreate(['id' => 1]);

        $data = $request->validated();
        $data['supported_locales_json'] = ['id', 'en'];

        $data['school_logo'] = $this->resolveBrandingAsset(
            $request->file('school_logo_file'),
            $data['school_logo'] ?? null,
            $setting->school_logo,
            'branding/logo'
        );

        $data['building_background'] = $this->resolveBrandingAsset(
            $request->file('building_background_file'),
            $data['building_background'] ?? null,
            $setting->building_background,
            'branding/background'
        );

        unset($data['school_logo_file'], $data['building_background_file']);

        $setting->update($data);

        return back()->with('success', 'Settings updated.');
    }

    private function resolveBrandingAsset(?UploadedFile $uploadedFile, ?string $manualPath, ?string $existingValue, string $directory): ?string
    {
        if ($uploadedFile !== null) {
            $stored = $uploadedFile->store($directory, 'public');

            return 'storage/'.$stored;
        }

        $manual = trim((string) ($manualPath ?? ''));
        if ($manual !== '') {
            return str_replace('\\', '/', $manual);
        }

        return $existingValue;
    }
}
