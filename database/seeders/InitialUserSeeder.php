<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::query()->where('name', Role::ADMIN)->firstOrFail();
        $guruRole = Role::query()->where('name', Role::GURU)->firstOrFail();
        $siswaRole = Role::query()->where('name', Role::SISWA)->firstOrFail();

        $admin = User::updateOrCreate(
            ['email' => 'admin@edusasana.sch.id'],
            [
                'name' => 'Admin Sekolah',
                'role_id' => $adminRole->id,
                'password' => Hash::make('password123'),
            ]
        );

        $guru = User::updateOrCreate(
            ['email' => 'guru@edusasana.sch.id'],
            [
                'name' => 'Guru Matematika',
                'role_id' => $guruRole->id,
                'password' => Hash::make('password123'),
            ]
        );

        $class = SchoolClass::updateOrCreate(
            ['code' => 'X-A'],
            [
                'name' => 'Kelas X-A',
                'grade_level' => 10,
                'homeroom_teacher_id' => $guru->id,
            ]
        );

        $siswa = User::updateOrCreate(
            ['email' => 'siswa@edusasana.sch.id'],
            [
                'name' => 'Siswa Demo',
                'role_id' => $siswaRole->id,
                'school_class_id' => $class->id,
                'password' => Hash::make('password123'),
            ]
        );

        $subject = Subject::updateOrCreate(
            ['name' => 'Matematika Dasar', 'school_class_id' => $class->id],
            [
                'code' => 'MTK-101',
                'teacher_id' => $guru->id,
                'description' => 'Materi pengantar matematika untuk kelas X.',
            ]
        );

        $subject->materials()->updateOrCreate(
            ['title' => 'Bilangan dan Operasi Dasar'],
            [
                'created_by' => $guru->id,
                'content' => 'Materi dasar mengenai bilangan bulat, pecahan, dan operasi hitung.',
                'published_at' => now(),
            ]
        );

        $admin->refresh();
        $guru->refresh();
        $siswa->refresh();
    }
}
