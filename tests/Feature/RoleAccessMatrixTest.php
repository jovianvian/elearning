<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAccessMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createBaseRoles();
    }

    public function test_dashboard_redirects_based_on_role(): void
    {
        $superAdmin = $this->makeUser(Role::SUPER_ADMIN);
        $this->actingAs($superAdmin)->get('/dashboard')->assertRedirect(route('dashboard.super-admin', [], false));

        $admin = $this->makeUser(Role::ADMIN);
        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('dashboard.admin', [], false));

        $principal = $this->makeUser(Role::PRINCIPAL);
        $this->actingAs($principal)->get('/dashboard')->assertRedirect(route('dashboard.principal', [], false));

        $teacher = $this->makeUser(Role::TEACHER);
        $this->actingAs($teacher)->get('/dashboard')->assertRedirect(route('dashboard.teacher', [], false));

        $student = $this->makeUser(Role::STUDENT, ['must_change_password' => false, 'nis' => '123456']);
        $this->actingAs($student)->get('/dashboard')->assertRedirect(route('dashboard.student', [], false));
    }

    public function test_admin_cannot_access_super_admin_only_routes(): void
    {
        $admin = $this->makeUser(Role::ADMIN);
        $this->actingAs($admin)->get(route('super-admin.settings.edit'))->assertForbidden();
        $this->actingAs($admin)->get(route('super-admin.audit-logs.index'))->assertForbidden();
    }

    public function test_principal_is_read_only_for_exam_area_and_cannot_open_crud_admin_pages(): void
    {
        $principal = $this->makeUser(Role::PRINCIPAL);

        $this->actingAs($principal)->get(route('exams.index'))->assertOk();
        $this->actingAs($principal)->get(route('reports.index'))->assertOk();

        $this->actingAs($principal)->get(route('users.index'))->assertForbidden();
        $this->actingAs($principal)->get(route('classes.index'))->assertForbidden();
        $this->actingAs($principal)->get(route('exams.create'))->assertForbidden();
    }

    public function test_teacher_can_access_teaching_modules_but_not_admin_modules(): void
    {
        $teacher = $this->makeUser(Role::TEACHER, ['nip' => '19870001']);

        $this->actingAs($teacher)->get(route('dashboard.teacher'))->assertOk();
        $this->actingAs($teacher)->get(route('question-banks.index'))->assertOk();
        $this->actingAs($teacher)->get(route('exams.create'))->assertOk();

        $this->actingAs($teacher)->get(route('users.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('super-admin.login-logs.index'))->assertForbidden();
    }

    public function test_student_can_only_access_student_routes_and_is_blocked_from_admin_exam_management(): void
    {
        $student = $this->makeUser(Role::STUDENT, ['must_change_password' => false, 'nis' => '555001']);

        $this->actingAs($student)->get(route('dashboard.student'))->assertOk();
        $this->actingAs($student)->get(route('student-exams.index'))->assertOk();
        $this->actingAs($student)->get(route('my-courses.index'))->assertOk();

        $this->actingAs($student)->get(route('exams.index'))->assertForbidden();
        $this->actingAs($student)->get(route('users.index'))->assertForbidden();
        $this->actingAs($student)->get(route('super-admin.settings.edit'))->assertForbidden();
    }

    public function test_student_with_must_change_password_is_forced_to_password_form(): void
    {
        $student = $this->makeUser(Role::STUDENT, ['must_change_password' => true, 'nis' => '881122']);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertRedirect(route('password.force.form', [], false));

        $this->actingAs($student)
            ->get(route('student-exams.index'))
            ->assertRedirect(route('password.force.form', [], false));
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
     * @param  array<string, mixed>  $overrides
     */
    private function makeUser(string $roleCode, array $overrides = []): User
    {
        $role = Role::query()->where('code', $roleCode)->firstOrFail();

        $defaults = [
            'role_id' => $role->id,
            'full_name' => ucfirst(str_replace('_', ' ', $roleCode)).' User',
            'username' => $roleCode.'_user_'.str_replace('.', '', (string) microtime(true)),
            'email' => $roleCode.'_'.uniqid('', true).'@example.test',
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
}
