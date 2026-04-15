<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\BillItem;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\StudentBill;
use App\Models\Subject;
use App\Models\User;
use App\Services\StudentBillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SppExamPaymentLockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createBaseRoles();
    }

    public function test_student_cannot_start_exam_if_bill_not_paid_until_required_month(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19910001']);
        $student = $this->makeUser(Role::STUDENT, ['nis' => '2701001']);
        [$course, $semester, $year] = $this->createCourseBundle($teacher, $student, 'GANJIL');

        $exam = Exam::query()->create([
            'course_id' => $course->id,
            'title' => 'UTS',
            'description' => null,
            'created_by' => $teacher->id,
            'exam_type' => Exam::TYPE_OBJECTIVE,
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 60,
            'max_attempts' => 1,
            'required_paid_month' => 10,
            'status' => Exam::STATUS_ACTIVE,
            'is_published' => true,
        ]);

        app(StudentBillService::class)->generateBill($student->id, $year->id, $semester, 100000);

        $this->actingAs($student)
            ->from(route('student-exams.index'))
            ->post(route('student-exams.start', $exam))
            ->assertRedirect(route('student-exams.index', [], false))
            ->assertSessionHas('error');
    }

    public function test_student_can_start_exam_after_paid_until_required_month(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19910002']);
        $student = $this->makeUser(Role::STUDENT, ['nis' => '2701002']);
        [$course, $semester, $year] = $this->createCourseBundle($teacher, $student, 'GANJIL');

        $exam = Exam::query()->create([
            'course_id' => $course->id,
            'title' => 'UAS',
            'description' => null,
            'created_by' => $teacher->id,
            'exam_type' => Exam::TYPE_OBJECTIVE,
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'duration_minutes' => 60,
            'max_attempts' => 1,
            'required_paid_month' => 10,
            'status' => Exam::STATUS_ACTIVE,
            'is_published' => true,
        ]);

        $service = app(StudentBillService::class);
        $bill = $service->generateBill($student->id, $year->id, $semester, 100000);
        $service->applyPayment($bill, [7, 8, 9, 10], 400000);

        $response = $this->actingAs($student)->post(route('student-exams.start', $exam));
        $response->assertRedirect();
        $this->assertStringContainsString('/student-exams/attempt/', $response->headers->get('Location'));
    }

    public function test_admin_can_record_manual_payment_and_bill_status_updates_correctly(): void
    {
        $admin = $this->makeUser(Role::ADMIN);
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19910003']);
        $student = $this->makeUser(Role::STUDENT, ['nis' => '2701003']);
        [, $semester, $year] = $this->createCourseBundle($teacher, $student, 'GANJIL');

        $service = app(StudentBillService::class);
        $bill = $service->generateBill($student->id, $year->id, $semester, 100000);

        $this->actingAs($admin)
            ->post(route('student-bills.payments.store', $bill), [
                'month_numbers' => [7, 8],
                'payment_amount' => 150000,
            ])
            ->assertRedirect();

        $bill->refresh();
        $this->assertSame(StudentBill::STATUS_PARTIAL, $bill->status);
        $this->assertSame(150000.0, (float) $bill->paid_amount);

        $july = BillItem::query()->where('student_bill_id', $bill->id)->where('month_number', 7)->firstOrFail();
        $august = BillItem::query()->where('student_bill_id', $bill->id)->where('month_number', 8)->firstOrFail();
        $this->assertSame(BillItem::STATUS_PAID, $july->status);
        $this->assertSame(BillItem::STATUS_PARTIAL, $august->status);
        $this->assertSame(50000.0, (float) $august->paid_amount);

        $this->actingAs($admin)
            ->post(route('student-bills.payments.store', $bill), [
                'month_numbers' => [8],
                'payment_amount' => 50000,
            ])
            ->assertRedirect();

        $august->refresh();
        $this->assertSame(BillItem::STATUS_PAID, $august->status);
        $this->assertSame(100000.0, (float) $august->paid_amount);
    }

    public function test_student_can_only_access_own_bill(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19910004']);
        $studentA = $this->makeUser(Role::STUDENT, ['nis' => '2701004']);
        $studentB = $this->makeUser(Role::STUDENT, ['nis' => '2701005']);
        [, $semester, $year] = $this->createCourseBundle($teacher, $studentA, 'GANJIL');

        $service = app(StudentBillService::class);
        $billA = $service->generateBill($studentA->id, $year->id, $semester, 100000);
        $billB = $service->generateBill($studentB->id, $year->id, $semester, 100000);

        $this->actingAs($studentA)
            ->get(route('my-bills.show', $billA))
            ->assertOk();

        $this->actingAs($studentA)
            ->get(route('my-bills.show', $billB))
            ->assertForbidden();
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
     * @return array{0: Course, 1: Semester, 2: AcademicYear}
     */
    private function createCourseBundle(User $teacher, User $student, string $semesterCode): array
    {
        $academicYear = AcademicYear::query()->create([
            'name' => '2025/2026 '.uniqid(),
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'is_active' => true,
        ]);

        $semester = Semester::query()->create([
            'academic_year_id' => $academicYear->id,
            'name' => strtolower($semesterCode) === 'genap' ? 'Genap' : 'Ganjil',
            'code' => $semesterCode.'_'.uniqid(),
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
            'name_en' => 'Mathematics',
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
        $course->students()->attach($student->id, ['enrolled_at' => now()]);

        return [$course, $semester, $academicYear];
    }
}

