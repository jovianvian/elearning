<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\LearningMaterial;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentMaterialProgress;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LearningMaterialModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createBaseRoles();
    }

    public function test_teacher_can_manage_material_for_owned_course_only(): void
    {
        $teacherA = $this->makeUser(Role::TEACHER, ['nip' => '19880001']);
        $teacherB = $this->makeUser(Role::TEACHER, ['nip' => '19880002']);
        [$courseA] = $this->createCourseBundle($teacherA);
        [$courseB] = $this->createCourseBundle($teacherB);

        $this->actingAs($teacherA)
            ->post(route('learning-materials.store'), [
                'course_id' => $courseA->id,
                'title' => 'Materi Guru A',
                'type' => LearningMaterial::TYPE_TEXT,
                'content' => 'Konten materi A',
                'sort_order' => 1,
                'is_published' => 1,
            ])
            ->assertRedirect(route('learning-materials.index', [], false));

        $materialA = LearningMaterial::query()->where('course_id', $courseA->id)->firstOrFail();

        $this->actingAs($teacherA)
            ->put(route('learning-materials.update', $materialA), [
                'course_id' => $courseA->id,
                'title' => 'Materi Guru A Updated',
                'type' => LearningMaterial::TYPE_TEXT,
                'content' => 'Konten baru',
                'sort_order' => 2,
                'is_published' => 1,
            ])
            ->assertRedirect(route('learning-materials.index', [], false));

        $this->actingAs($teacherA)
            ->post(route('learning-materials.store'), [
                'course_id' => $courseB->id,
                'title' => 'Materi Ilegal',
                'type' => LearningMaterial::TYPE_TEXT,
                'content' => 'Tidak boleh',
                'sort_order' => 1,
            ])
            ->assertForbidden();
    }

    public function test_student_can_only_view_published_materials_from_enrolled_course(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19880003']);
        $studentA = $this->makeUser(Role::STUDENT, ['nis' => '2601001']);
        $studentB = $this->makeUser(Role::STUDENT, ['nis' => '2601002']);

        [$courseA] = $this->createCourseBundle($teacher, $studentA);
        [$courseB] = $this->createCourseBundle($teacher, $studentB);

        $publishedOwned = LearningMaterial::query()->create([
            'course_id' => $courseA->id,
            'title' => 'Publik A',
            'type' => LearningMaterial::TYPE_TEXT,
            'content' => 'Materi publik course A',
            'is_published' => true,
            'published_at' => now(),
            'created_by' => $teacher->id,
            'updated_by' => $teacher->id,
        ]);
        $draftOwned = LearningMaterial::query()->create([
            'course_id' => $courseA->id,
            'title' => 'Draft A',
            'type' => LearningMaterial::TYPE_TEXT,
            'content' => 'Draft',
            'is_published' => false,
            'created_by' => $teacher->id,
            'updated_by' => $teacher->id,
        ]);
        $publishedOther = LearningMaterial::query()->create([
            'course_id' => $courseB->id,
            'title' => 'Publik B',
            'type' => LearningMaterial::TYPE_TEXT,
            'content' => 'Materi publik course B',
            'is_published' => true,
            'published_at' => now(),
            'created_by' => $teacher->id,
            'updated_by' => $teacher->id,
        ]);

        $this->actingAs($studentA)
            ->get(route('student-materials.index'))
            ->assertOk()
            ->assertSee($publishedOwned->title)
            ->assertDontSee($draftOwned->title)
            ->assertDontSee($publishedOther->title);

        $this->actingAs($studentA)
            ->get(route('student-materials.show', $publishedOwned))
            ->assertOk();

        $this->actingAs($studentA)
            ->get(route('student-materials.show', $draftOwned))
            ->assertForbidden();

        $this->actingAs($studentA)
            ->get(route('student-materials.show', $publishedOther))
            ->assertForbidden();
    }

    public function test_progress_is_recorded_on_open_and_can_be_marked_completed(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19880004']);
        $student = $this->makeUser(Role::STUDENT, ['nis' => '2602001']);
        [$course] = $this->createCourseBundle($teacher, $student);

        $material = LearningMaterial::query()->create([
            'course_id' => $course->id,
            'title' => 'Materi Progres',
            'type' => LearningMaterial::TYPE_TEXT,
            'content' => 'Baca materi ini',
            'is_published' => true,
            'published_at' => now(),
            'created_by' => $teacher->id,
            'updated_by' => $teacher->id,
        ]);

        $this->actingAs($student)
            ->get(route('student-materials.show', $material))
            ->assertOk();

        $progress = StudentMaterialProgress::query()
            ->where('learning_material_id', $material->id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $this->assertSame(StudentMaterialProgress::STATUS_IN_PROGRESS, $progress->status);
        $this->assertNotNull($progress->first_opened_at);
        $this->assertNotNull($progress->last_accessed_at);

        $this->actingAs($student)
            ->post(route('student-materials.complete', $material))
            ->assertRedirect();

        $progress->refresh();
        $this->assertSame(StudentMaterialProgress::STATUS_COMPLETED, $progress->status);
        $this->assertNotNull($progress->completed_at);
    }

    public function test_admin_can_manage_material_across_courses(): void
    {
        $admin = $this->makeUser(Role::ADMIN);
        $teacherA = $this->makeUser(Role::TEACHER, ['nip' => '19880005']);
        $teacherB = $this->makeUser(Role::TEACHER, ['nip' => '19880006']);
        [$courseA] = $this->createCourseBundle($teacherA);
        [$courseB] = $this->createCourseBundle($teacherB);

        $this->actingAs($admin)
            ->post(route('learning-materials.store'), [
                'course_id' => $courseB->id,
                'title' => 'Materi oleh Admin',
                'type' => LearningMaterial::TYPE_TEXT,
                'content' => 'Konten admin',
                'is_published' => 1,
            ])
            ->assertRedirect(route('learning-materials.index', [], false));

        $material = LearningMaterial::query()->where('course_id', $courseB->id)->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('learning-materials.toggle-publish', $material))
            ->assertRedirect();

        $this->assertFalse((bool) $material->fresh()->is_published);

        $this->actingAs($admin)
            ->put(route('learning-materials.update', $material), [
                'course_id' => $courseA->id,
                'title' => 'Materi dipindah',
                'type' => LearningMaterial::TYPE_TEXT,
                'content' => 'Update admin',
                'sort_order' => 3,
                'is_published' => 1,
            ])
            ->assertRedirect(route('learning-materials.index', [], false));

        $this->assertSame($courseA->id, (int) $material->fresh()->course_id);
    }

    public function test_super_admin_can_manage_material_across_courses(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19880007']);
        [$course] = $this->createCourseBundle($teacher);

        $this->actingAs($superAdmin)
            ->post(route('learning-materials.store'), [
                'course_id' => $course->id,
                'title' => 'Materi Super Admin',
                'type' => LearningMaterial::TYPE_TEXT,
                'content' => 'Konten super admin',
                'is_published' => 1,
            ])
            ->assertRedirect(route('learning-materials.index', [], false));

        $material = LearningMaterial::query()->where('title', 'Materi Super Admin')->firstOrFail();

        $this->actingAs($superAdmin)
            ->delete(route('learning-materials.destroy', $material))
            ->assertRedirect(route('learning-materials.index', [], false));

        $this->assertDatabaseMissing('learning_materials', ['id' => $material->id]);
    }

    private function createBaseRoles(): void
    {
        foreach ([
            [Role::SUPER_ADMIN, 'Super Admin'],
            [Role::ADMIN, 'Admin'],
            [Role::PRINCIPAL, 'Principal'],
            [Role::TEACHER, 'Teacher'],
            [Role::STUDENT, 'Student'],
        ] as [$code, $name]) {
            DB::table('roles')->insert([
                'name' => $name,
                'display_name' => $name,
                'code' => $code,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @param  array<string,mixed>  $overrides
     */
    private function makeUser(string $roleCode, array $overrides = []): User
    {
        $role = Role::query()->where('code', $roleCode)->firstOrFail();

        $defaults = [
            'role_id' => $role->id,
            'full_name' => ucfirst(str_replace('_', ' ', $roleCode)).' '.uniqid(),
            'username' => $roleCode.'_'.uniqid(),
            'email' => $roleCode.'_'.uniqid().'@example.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'must_change_password' => false,
        ];

        if ($roleCode === Role::STUDENT && ! isset($overrides['nis'])) {
            $defaults['nis'] = (string) random_int(100000, 999999);
        }

        if ($roleCode === Role::TEACHER && ! isset($overrides['nip'])) {
            $defaults['nip'] = (string) random_int(10000000, 99999999);
        }

        return User::query()->create(array_merge($defaults, $overrides));
    }

    /**
     * @return array{0: Course}
     */
    private function createCourseBundle(User $teacher, ?User $student = null): array
    {
        $academicYear = AcademicYear::query()->create([
            'name' => '2025/2026 '.uniqid(),
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $semester = Semester::query()->create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Ganjil',
            'code' => 'GANJIL_'.uniqid(),
            'start_date' => '2025-07-01',
            'end_date' => '2025-12-31',
            'is_active' => true,
        ]);

        $class = SchoolClass::query()->create([
            'name' => '7A '.uniqid(),
            'code' => '7A_'.uniqid(),
            'grade_level' => 7,
            'academic_year_id' => $academicYear->id,
            'is_active' => true,
        ]);

        $subject = Subject::query()->create([
            'name_id' => 'Matematika '.uniqid(),
            'name_en' => 'Math',
            'code' => 'MATH_'.uniqid(),
            'is_active' => true,
        ]);

        $course = Course::query()->create([
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'title' => 'Course '.uniqid(),
            'slug' => 'course-'.uniqid(),
            'description' => null,
            'is_published' => true,
            'created_by' => $teacher->id,
        ]);

        $course->teachers()->attach($teacher->id, ['is_main_teacher' => true]);
        if ($student) {
            $course->students()->attach($student->id, ['enrolled_at' => now()]);
        }

        return [$course];
    }
}

