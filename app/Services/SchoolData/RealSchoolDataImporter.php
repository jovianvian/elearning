<?php

namespace App\Services\SchoolData;

use App\Models\AcademicYear;
use App\Models\ClassStudent;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use App\Models\User;
use App\Models\UserProfile;
use App\Support\SimpleXlsxReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RealSchoolDataImporter
{
    public function __construct(
        private readonly SimpleXlsxReader $xlsxReader
    ) {}

    /**
     * @return array<string, int|string>
     */
    public function import(string $teacherFilePath, string $studentFilePath): array
    {
        $teacherRows = $this->xlsxReader->readFirstSheetRows($teacherFilePath);
        $studentRows = $this->xlsxReader->readFirstSheetRows($studentFilePath);

        $teacherData = $this->extractTeacherData($teacherRows);
        $studentData = $this->extractStudentData($studentRows);

        $roles = Role::query()->whereIn('code', [
            Role::SUPER_ADMIN,
            Role::ADMIN,
            Role::PRINCIPAL,
            Role::TEACHER,
            Role::STUDENT,
        ])->get()->keyBy('code');

        foreach ([Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL, Role::TEACHER, Role::STUDENT] as $code) {
            if (! isset($roles[$code])) {
                throw new InvalidArgumentException("Missing required role: {$code}");
            }
        }

        $activeYear = AcademicYear::query()->where('is_active', true)->first();
        if (! $activeYear) {
            throw new InvalidArgumentException('No active academic year found.');
        }

        $systemUsers = $this->ensureSystemUsers($roles);

        $this->cleanupAcademicDataAndDummyUsers(
            keepUserIds: $systemUsers->pluck('id')->all(),
            keepRoleIds: [$roles[Role::SUPER_ADMIN]->id, $roles[Role::ADMIN]->id]
        );

        $classes = $this->ensureMappedClasses($activeYear->id);

        $principalCount = 0;
        $vicePrincipalCount = 0;
        $teacherUsers = [];

        foreach ($teacherData as $teacher) {
            $jenisPtk = Str::lower($teacher['jenis_ptk']);

            if (str_contains($jenisPtk, 'kepala sekolah') && ! str_contains($jenisPtk, 'wakil')) {
                $this->upsertLeadershipUser($teacher, $roles[Role::PRINCIPAL]->id, 'principal');
                $principalCount++;
                continue;
            }

            if (str_contains($jenisPtk, 'wakil')) {
                $this->upsertLeadershipUser($teacher, $roles[Role::PRINCIPAL]->id, 'vice-principal');
                $vicePrincipalCount++;
                continue;
            }

            if (! str_contains($jenisPtk, 'guru')) {
                continue;
            }

            $teacherUsers[] = $this->upsertTeacherUser($teacher, $roles[Role::TEACHER]->id);
        }

        $teacherUsers = collect($teacherUsers)->unique('id')->values();
        $this->syncTeacherSubjectAssignments($teacherUsers, $activeYear->id);

        $studentCount = 0;
        foreach ($studentData as $student) {
            $mappedClass = $this->mapSourceClassToTargetClass($student['source_class']);
            if (! $mappedClass) {
                continue;
            }

            $class = $classes[$mappedClass['target']] ?? null;
            if (! $class) {
                continue;
            }

            $user = $this->upsertStudentUser($student, $roles[Role::STUDENT]->id, $class->id);

            ClassStudent::query()->updateOrCreate(
                ['student_id' => $user->id, 'academic_year_id' => $activeYear->id],
                ['class_id' => $class->id, 'status' => 'active']
            );

            UserProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'preferred_language' => 'id',
                    'gender' => $this->mapGender($student['gender']),
                    'birth_date' => $this->normalizeDate($student['birth_date']),
                    'birth_place' => $student['birth_place'] ?: null,
                    'religion' => $student['religion'] ?: null,
                    'address' => $student['address'] ?: null,
                    'source_class_name' => $student['source_class'] ?: null,
                    'parent_name' => $student['parent_name'] ?: null,
                    'parent_birth_year' => $student['parent_birth_year'] ?: null,
                    'parent_education' => $student['parent_education'] ?: null,
                    'employment_status' => null,
                    'ptk_type' => null,
                    'bio' => "source_class: {$student['source_class']}",
                ]
            );

            $studentCount++;
        }

        return [
            'teachers_imported' => $teacherUsers->count(),
            'principals_imported' => $principalCount,
            'vice_principals_imported' => $vicePrincipalCount,
            'students_imported' => $studentCount,
            'active_year' => $activeYear->name,
        ];
    }

    /**
     * @param  array<int, array<string, string>>  $teacherData
     */
    private function upsertTeacherUser(array $teacher, int $teacherRoleId): User
    {
        $name = $this->normalizeName($teacher['name']);
        $nip = $this->normalizeIdentifier($teacher['nip']);
        $username = $this->generateUniqueUsername('teacher', $name);

        $email = $this->buildEmailFromUsername($username);
        $passwordSeed = $nip ?: $username;

        $user = User::query()->updateOrCreate(
            ['full_name' => $name, 'role_id' => $teacherRoleId],
            [
                'username' => $username,
                'email' => $email,
                'nip' => $nip ?: null,
                'password' => Hash::make($passwordSeed),
                'is_active' => true,
                'must_change_password' => false,
                'school_class_id' => null,
                'nis' => null,
            ]
        );

        UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'preferred_language' => 'id',
                'gender' => $this->mapGender($teacher['gender']),
                'birth_date' => $this->normalizeDate($teacher['birth_date']),
                'birth_place' => $teacher['birth_place'] ?: null,
                'religion' => $teacher['religion'] ?: null,
                'address' => $teacher['address'] ?: null,
                'employment_status' => $teacher['employment_status'] ?: null,
                'ptk_type' => $teacher['jenis_ptk'] ?: null,
                'source_class_name' => null,
                'parent_name' => null,
                'parent_birth_year' => null,
                'parent_education' => null,
            ]
        );

        return $user;
    }

    /**
     * @param  array<string, string>  $teacher
     */
    private function upsertLeadershipUser(array $teacher, int $roleId, string $prefix): User
    {
        $name = $this->normalizeName($teacher['name']);
        $nip = $this->normalizeIdentifier($teacher['nip']);
        $username = $this->generateUniqueUsername($prefix, $name);
        $email = $this->buildEmailFromUsername($username);

        $user = User::query()->updateOrCreate(
            ['full_name' => $name, 'role_id' => $roleId],
            [
                'username' => $username,
                'email' => $email,
                'nip' => $nip ?: null,
                'password' => Hash::make($nip ?: $username),
                'is_active' => true,
                'must_change_password' => false,
                'school_class_id' => null,
                'nis' => null,
            ]
        );

        UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'preferred_language' => 'id',
                'gender' => $this->mapGender($teacher['gender']),
                'birth_date' => $this->normalizeDate($teacher['birth_date']),
                'birth_place' => $teacher['birth_place'] ?: null,
                'religion' => $teacher['religion'] ?: null,
                'address' => $teacher['address'] ?: null,
                'employment_status' => $teacher['employment_status'] ?: null,
                'ptk_type' => $teacher['jenis_ptk'] ?: null,
                'source_class_name' => null,
                'parent_name' => null,
                'parent_birth_year' => null,
                'parent_education' => null,
            ]
        );

        return $user;
    }

    /**
     * @param  array<string, string>  $student
     */
    private function upsertStudentUser(array $student, int $studentRoleId, int $classId): User
    {
        $nis = $this->normalizeIdentifier($student['nipd']);
        $name = $this->normalizeName($student['name']);
        $username = $this->generateUniqueUsername('student', $name, $nis);

        return User::query()->updateOrCreate(
            ['nis' => $nis],
            [
                'role_id' => $studentRoleId,
                'school_class_id' => $classId,
                'full_name' => $name,
                'username' => $username,
                'email' => null,
                'nip' => null,
                'password' => Hash::make($nis),
                'is_active' => true,
                'must_change_password' => true,
            ]
        );
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function ensureSystemUsers($roles)
    {
        $superAdmin = User::query()->updateOrCreate(
            ['username' => 'superadmin'],
            [
                'role_id' => $roles[Role::SUPER_ADMIN]->id,
                'full_name' => 'Rama Aditya',
                'email' => 'superadmin@teramia.sch.id',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );

        $admin = User::query()->updateOrCreate(
            ['username' => 'admin'],
            [
                'role_id' => $roles[Role::ADMIN]->id,
                'full_name' => 'Dewi Lestari',
                'email' => 'admin@teramia.sch.id',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
                'must_change_password' => false,
            ]
        );

        UserProfile::query()->updateOrCreate(['user_id' => $superAdmin->id], ['preferred_language' => 'id']);
        UserProfile::query()->updateOrCreate(['user_id' => $admin->id], ['preferred_language' => 'id']);

        return collect([$superAdmin, $admin]);
    }

    /**
     * @param  array<int, int>  $keepUserIds
     * @param  array<int, int>  $keepRoleIds
     */
    private function cleanupAcademicDataAndDummyUsers(array $keepUserIds, array $keepRoleIds): void
    {
        $tablesToTruncate = [
            'exam_session_logs',
            'tab_switch_logs',
            'suspicious_activity_logs',
            'exam_attempt_answers',
            'exam_attempts',
            'exam_questions',
            'exam_publication_logs',
            'exams',
            'question_options',
            'questions',
            'question_import_logs',
            'question_banks',
            'course_students',
            'course_teachers',
            'courses',
            'user_notifications',
            'notifications',
            'restore_logs',
            'activity_logs',
            'login_logs',
            'class_students',
            'subject_teachers',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($tablesToTruncate as $table) {
                DB::table($table)->truncate();
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        UserProfile::query()->whereNotIn('user_id', $keepUserIds)->delete();

        User::withTrashed()
            ->whereNotIn('id', $keepUserIds)
            ->whereNotIn('role_id', $keepRoleIds)
            ->get()
            ->each(fn (User $user) => $user->forceDelete());
    }

    /**
     * @return array<string, SchoolClass>
     */
    private function ensureMappedClasses(int $academicYearId): array
    {
        $map = [
            '7A' => ['grade' => 7],
            '7B' => ['grade' => 7],
            '7C' => ['grade' => 7],
            '8A' => ['grade' => 8],
            '8B' => ['grade' => 8],
            '8C' => ['grade' => 8],
            '9A' => ['grade' => 9],
            '9B' => ['grade' => 9],
            '9C' => ['grade' => 9],
        ];

        $classes = [];
        foreach ($map as $className => $meta) {
            $classes[$className] = SchoolClass::query()->updateOrCreate(
                ['name' => $className, 'academic_year_id' => $academicYearId],
                [
                    'code' => $className,
                    'grade_level' => $meta['grade'],
                    'is_active' => true,
                ]
            );
        }

        return $classes;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $teachers
     */
    private function syncTeacherSubjectAssignments($teachers, int $academicYearId): void
    {
        $subjects = Subject::query()->where('is_active', true)->orderBy('code')->get()->values();
        if ($subjects->isEmpty()) {
            return;
        }

        SubjectTeacher::query()->where('academic_year_id', $academicYearId)->delete();

        foreach ($teachers->values() as $i => $teacher) {
            $subject = $subjects[$i % $subjects->count()];
            SubjectTeacher::query()->create([
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
                'academic_year_id' => $academicYearId,
                'is_active' => true,
            ]);
        }
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<int, array<string, string>>
     */
    private function extractTeacherData(array $rows): array
    {
        $headerRow = $this->findHeaderRow($rows, ['No', 'Nama', 'Jenis PTK']);
        if ($headerRow === null) {
            throw new InvalidArgumentException('Teacher sheet header row not found.');
        }

        $result = [];
        for ($i = $headerRow + 1; $i <= count($rows); $i++) {
            $row = $rows[$i - 1] ?? [];
            $name = trim((string) ($row[2] ?? ''));
            $jenisPtk = trim((string) ($row[9] ?? ''));

            if ($name === '' || Str::lower($name) === 'nama') {
                continue;
            }

            if ($jenisPtk === '' || Str::lower($jenisPtk) === 'jenis ptk') {
                continue;
            }

            $result[] = [
                'name' => $name,
                'nuptk' => (string) ($row[3] ?? ''),
                'gender' => (string) ($row[4] ?? ''),
                'birth_place' => (string) ($row[5] ?? ''),
                'birth_date' => (string) ($row[6] ?? ''),
                'nip' => (string) ($row[7] ?? ''),
                'employment_status' => (string) ($row[8] ?? ''),
                'jenis_ptk' => $jenisPtk,
                'religion' => (string) ($row[10] ?? ''),
                'address' => (string) ($row[11] ?? ''),
            ];
        }

        return $result;
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @return array<int, array<string, string>>
     */
    private function extractStudentData(array $rows): array
    {
        $headerRow = $this->findHeaderRow($rows, ['Nama', 'NIPD', 'No']);
        if ($headerRow === null) {
            throw new InvalidArgumentException('Student sheet header row not found.');
        }

        $result = [];
        for ($i = $headerRow + 1; $i <= count($rows); $i++) {
            $row = $rows[$i - 1] ?? [];

            $sourceClass = trim((string) ($row[2] ?? ''));
            $name = trim((string) ($row[3] ?? ''));
            $nipd = trim((string) ($row[4] ?? ''));

            if ($name === '' || $nipd === '' || Str::lower($name) === 'nama') {
                continue;
            }

            $result[] = [
                'source_class' => $sourceClass,
                'name' => $name,
                'nipd' => $nipd,
                'gender' => (string) ($row[5] ?? ''),
                'nisn' => (string) ($row[6] ?? ''),
                'birth_place' => (string) ($row[7] ?? ''),
                'birth_date' => (string) ($row[8] ?? ''),
                'religion' => (string) ($row[9] ?? ''),
                'address' => (string) ($row[10] ?? ''),
                'parent_name' => (string) ($row[18] ?? ''),
                'parent_birth_year' => (string) ($row[19] ?? ''),
                'parent_education' => (string) ($row[20] ?? ''),
            ];
        }

        return $result;
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     * @param  array<int, string>  $requiredLabels
     */
    private function findHeaderRow(array $rows, array $requiredLabels): ?int
    {
        foreach ($rows as $idx => $row) {
            $values = collect($row)
                ->map(fn ($v) => Str::lower(trim((string) $v)))
                ->filter()
                ->values()
                ->all();

            $ok = true;
            foreach ($requiredLabels as $label) {
                if (! in_array(Str::lower($label), $values, true)) {
                    $ok = false;
                    break;
                }
            }

            if ($ok) {
                return $idx + 1; // 1-based row index
            }
        }

        return null;
    }

    /**
     * @return array{target: string, grade: int}|null
     */
    private function mapSourceClassToTargetClass(string $source): ?array
    {
        $value = preg_replace('/\s+/u', ' ', trim($source));
        if (! $value) {
            return null;
        }

        if (preg_match('/^(7|8|9)\s*([A-C])$/i', $value, $m)) {
            return ['target' => strtoupper($m[1].$m[2]), 'grade' => (int) $m[1]];
        }

        if (! preg_match('/^(Obedience|Compassion|Wisdom)\s*([A-C])$/i', $value, $m)) {
            return null;
        }

        $gradeMap = [
            'obedience' => 7,
            'compassion' => 8,
            'wisdom' => 9,
        ];

        $prefix = Str::lower($m[1]);
        $grade = $gradeMap[$prefix] ?? null;
        if (! $grade) {
            return null;
        }

        return ['target' => $grade.strtoupper($m[2]), 'grade' => $grade];
    }

    private function normalizeIdentifier(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\.0$/', '', $value);
        $value = preg_replace('/\s+/u', '', $value);

        return (string) ($value ?? '');
    }

    private function normalizeName(string $name): string
    {
        $name = preg_replace('/\s+/u', ' ', trim($name));
        if (! $name) {
            return '';
        }

        return (string) Str::of(Str::lower($name))->title();
    }

    private function normalizeDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        return null;
    }

    private function mapGender(?string $value): ?string
    {
        $v = Str::lower(trim((string) $value));

        return match ($v) {
            'l', 'male', 'm' => 'male',
            'p', 'female', 'f' => 'female',
            default => null,
        };
    }

    private function buildEmailFromUsername(string $username): string
    {
        return "{$username}@teramia.sch.id";
    }

    private function generateUniqueUsername(string $prefix, string $name, ?string $forcedSuffix = null): string
    {
        $base = Str::slug($name, '.');
        if ($base === '') {
            $base = 'user';
        }

        if ($forcedSuffix) {
            $candidate = Str::lower("{$prefix}.{$forcedSuffix}");
        } else {
            $candidate = Str::lower("{$prefix}.{$base}");
        }

        if (! User::withTrashed()->where('username', $candidate)->exists()) {
            return $candidate;
        }

        $counter = 2;
        while (true) {
            $next = "{$candidate}.{$counter}";
            if (! User::withTrashed()->where('username', $next)->exists()) {
                return $next;
            }
            $counter++;
        }
    }
}
