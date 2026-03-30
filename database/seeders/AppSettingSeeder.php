<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AppSetting;
use App\Models\Semester;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSemester = Semester::where('is_active', true)->first();

        AppSetting::updateOrCreate(
            ['id' => 1],
            [
                'app_name' => 'Teramia E-Learning',
                'school_name' => 'SMP Teramia',
                'primary_color' => '#1D4ED8',
                'secondary_color' => '#1E3A8A',
                'accent_color' => '#FACC15',
                'default_locale' => 'id',
                'supported_locales_json' => ['id', 'en'],
                'footer_text' => 'Teramia E-Learning',
                'school_email' => 'info@teramia.sch.id',
                'school_phone' => '+62-000-000-000',
                'school_address' => 'SMP Teramia',
                'active_academic_year_id' => $activeYear?->id,
                'active_semester_id' => $activeSemester?->id,
            ]
        );
    }
}
