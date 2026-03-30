<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $guru = User::whereHas('role', fn ($q) => $q->where('name', Role::GURU))->first();

        if (! $guru) {
            return;
        }

        $class = SchoolClass::firstOrCreate(
            ['code' => 'X-B'],
            ['name' => 'Kelas X-B', 'grade_level' => 10]
        );

        $subject = Subject::firstOrCreate(
            ['name' => 'Bahasa Indonesia', 'school_class_id' => $class->id],
            ['code' => 'BIN-101', 'teacher_id' => $guru->id, 'description' => 'Pengantar bahasa Indonesia.']
        );

        Material::firstOrCreate(
            ['title' => 'Teks Eksposisi Dasar', 'subject_id' => $subject->id],
            [
                'created_by' => $guru->id,
                'content' => 'Materi dasar tentang struktur teks eksposisi.',
                'published_at' => now(),
            ]
        );
    }
}
