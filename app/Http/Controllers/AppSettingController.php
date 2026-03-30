<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAppSettingRequest;
use App\Models\AcademicYear;
use App\Models\AppSetting;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
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

        $setting->update($data);

        return back()->with('success', 'Settings updated.');
    }
}
