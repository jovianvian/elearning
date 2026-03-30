<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassStudent;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('code', Role::SUPER_ADMIN)->firstOrFail();
        $adminRole = Role::where('code', Role::ADMIN)->firstOrFail();
        $principalRole = Role::where('code', Role::PRINCIPAL)->firstOrFail();
        $teacherRole = Role::where('code', Role::TEACHER)->firstOrFail();
        $studentRole = Role::where('code', Role::STUDENT)->firstOrFail();

        $superAdmin = User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'role_id' => $superAdminRole->id,
                'full_name' => 'Rama Aditya',
                'email' => 'superadmin@teramia.sch.id',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );

        $admin1 = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'role_id' => $adminRole->id,
                'full_name' => 'Dewi Lestari',
                'email' => 'admin@teramia.sch.id',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );

        $admin2 = User::updateOrCreate(
            ['username' => 'admin.ops'],
            [
                'role_id' => $adminRole->id,
                'full_name' => 'Yusuf Hidayat',
                'email' => 'admin.ops@teramia.sch.id',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );

        $principal = User::updateOrCreate(
            ['username' => 'principal'],
            [
                'role_id' => $principalRole->id,
                'full_name' => 'Maria Simanjuntak',
                'email' => 'principal@teramia.sch.id',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );

        $year = AcademicYear::where('is_active', true)->firstOrFail();

        $teacherSeed = [
            ['name' => 'Agus Saputra', 'subject_code' => 'MTK'],
            ['name' => 'Rina Melati', 'subject_code' => 'MTK'],
            ['name' => 'Benny Halim', 'subject_code' => 'IPA'],
            ['name' => 'Siska Natalia', 'subject_code' => 'IPA'],
            ['name' => 'Fajar Setiawan', 'subject_code' => 'BIN'],
            ['name' => 'Monica Kurnia', 'subject_code' => 'BIG'],
            ['name' => 'Slamet Widodo', 'subject_code' => 'PPKN'],
            ['name' => 'Yohana Manurung', 'subject_code' => 'AGR'],
            ['name' => 'Ricky Gunawan', 'subject_code' => 'IPS'],
            ['name' => 'Teguh Prakoso', 'subject_code' => 'PJOK'],
            ['name' => 'Kevin Jonathan', 'subject_code' => 'INF'],
            ['name' => 'Nadia Siregar', 'subject_code' => 'SBK'],
            ['name' => 'Lina Oktaviani', 'subject_code' => 'PRK'],
        ];

        $nipCounter = 198700100000;
        foreach ($teacherSeed as $seed) {
            $username = 'teacher.'.Str::slug(strtolower(explode(' ', $seed['name'])[0]), '');
            $teacher = User::updateOrCreate(
                ['username' => $username],
                [
                    'role_id' => $teacherRole->id,
                    'full_name' => $seed['name'],
                    'email' => $username.'@teramia.sch.id',
                    'nip' => (string) $nipCounter,
                    'password' => Hash::make('Password123!'),
                    'is_active' => true,
                    'must_change_password' => false,
                ]
            );
            $nipCounter++;

            $subject = Subject::where('code', $seed['subject_code'])->first();
            if ($subject) {
                SubjectTeacher::updateOrCreate(
                    ['teacher_id' => $teacher->id, 'subject_id' => $subject->id, 'academic_year_id' => $year->id],
                    ['is_active' => true]
                );
            }

            UserProfile::updateOrCreate(
                ['user_id' => $teacher->id],
                ['preferred_language' => 'id']
            );
        }

        $classNames = ['7A','7B','7C','8A','8B','8C','9A','9B','9C'];
        $classes = SchoolClass::whereIn('name', $classNames)->orderBy('name')->get()->keyBy('name');
        $studentNames = $this->generateStudentNames(189);

        $studentIndex = 0;
        foreach ($classNames as $classPos => $className) {
            $class = $classes[$className];
            $classSerial = $classPos + 1;

            for ($i = 1; $i <= 21; $i++) {
                $name = $studentNames[$studentIndex];
                $nis = '2526'.$classSerial.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $username = 'stu'.$nis;
                $email = $studentIndex % 3 === 0 ? strtolower(Str::slug($name, '.')).'@mail.com' : null;

                $student = User::updateOrCreate(
                    ['nis' => $nis],
                    [
                        'role_id' => $studentRole->id,
                        'full_name' => $name,
                        'username' => $username,
                        'school_class_id' => $class->id,
                        'email' => $email,
                        'password' => Hash::make($nis),
                        'is_active' => true,
                        'must_change_password' => true,
                    ]
                );

                ClassStudent::updateOrCreate(
                    ['student_id' => $student->id, 'academic_year_id' => $year->id],
                    ['class_id' => $class->id, 'status' => 'active']
                );

                UserProfile::updateOrCreate(
                    ['user_id' => $student->id],
                    ['preferred_language' => $studentIndex % 5 === 0 ? 'en' : 'id']
                );

                $studentIndex++;
            }
        }

        foreach ([$superAdmin, $admin1, $admin2, $principal] as $coreUser) {
            UserProfile::updateOrCreate(
                ['user_id' => $coreUser->id],
                ['preferred_language' => 'id']
            );
        }
    }

    private function generateStudentNames(int $total): array
    {
        $firstNames = [
            'Andi', 'Budi', 'Candra', 'Dimas', 'Eko', 'Fajar', 'Galih', 'Hendra', 'Indra', 'Joko',
            'Kevin', 'Lukas', 'Mario', 'Nanda', 'Oscar', 'Pandu', 'Raka', 'Satria', 'Teguh', 'Vino',
            'Wahyu', 'Yoga', 'Zidan', 'Ayu', 'Bella', 'Citra', 'Dewi', 'Elsa', 'Fitri', 'Gita',
            'Hana', 'Intan', 'Jihan', 'Kartika', 'Lia', 'Maya', 'Nadia', 'Olivia', 'Putri', 'Qonita',
            'Rani', 'Salsa', 'Tiara', 'Uli', 'Vania', 'Wulan', 'Yuni', 'Zahra', 'Clara', 'Monica',
        ];

        $lastNames = [
            'Pratama', 'Santoso', 'Wijaya', 'Saputra', 'Nugroho', 'Setiawan', 'Permata', 'Kurniawan', 'Hidayat', 'Gunawan',
            'Siregar', 'Simanjuntak', 'Manurung', 'Lumban Gaol', 'Pakpahan', 'Tampubolon', 'Sitompul', 'Hutapea', 'Pangaribuan', 'Marbun',
            'Halim', 'Lesmana', 'Putra', 'Putri', 'Ramadhan', 'Hermawan', 'Yuliana', 'Lestari', 'Oktaviani', 'Maharani',
            'Syahputra', 'Natalia', 'Kristina', 'Jonathan', 'Rantung', 'Silalahi', 'Sihombing', 'Parhusip', 'Harefa', 'Nainggolan',
        ];

        $names = [];
        $used = [];

        while (count($names) < $total) {
            $first = $firstNames[array_rand($firstNames)];
            $last = $lastNames[array_rand($lastNames)];
            $full = $first.' '.$last;

            if (isset($used[$full])) {
                continue;
            }

            $used[$full] = true;
            $names[] = $full;
        }

        return $names;
    }
}
