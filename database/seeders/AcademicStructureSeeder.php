<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::updateOrCreate(
            ['name' => '2025/2026'],
            ['start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]
        );

        Semester::updateOrCreate(
            ['academic_year_id' => $year->id, 'code' => 'odd'],
            ['name' => 'Ganjil', 'start_date' => '2025-07-01', 'end_date' => '2025-12-31', 'is_active' => true]
        );

        Semester::updateOrCreate(
            ['academic_year_id' => $year->id, 'code' => 'even'],
            ['name' => 'Genap', 'start_date' => '2026-01-01', 'end_date' => '2026-06-30', 'is_active' => false]
        );

        $classNames = ['7A','7B','7C','8A','8B','8C','9A','9B','9C'];
        foreach ($classNames as $className) {
            SchoolClass::updateOrCreate(
                ['name' => $className],
                [
                    'code' => $className,
                    'grade_level' => (int) substr($className, 0, 1),
                    'academic_year_id' => $year->id,
                    'is_active' => true,
                ]
            );
        }

        $subjects = [
            ['Matematika', 'Mathematics', 'MTK'],
            ['PPKn', 'Civics', 'PPKN'],
            ['Bahasa Indonesia', 'Indonesian Language', 'BIN'],
            ['Agama Kristen', 'Christian Religion', 'AGR'],
            ['Bahasa Inggris', 'English', 'BIG'],
            ['IPA', 'Science', 'IPA'],
            ['IPS', 'Social Studies', 'IPS'],
            ['PJOK', 'Physical Education', 'PJOK'],
            ['Informatika', 'Informatics', 'INF'],
            ['Seni Budaya', 'Cultural Arts', 'SBK'],
            ['Prakarya', 'Craft', 'PRK'],
        ];

        foreach ($subjects as [$idName, $enName, $code]) {
            Subject::updateOrCreate(
                ['code' => $code],
                ['name_id' => $idName, 'name_en' => $enName, 'description' => $idName, 'is_active' => true]
            );
        }
    }
}
