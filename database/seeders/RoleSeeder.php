<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'display_name' => 'Super Admin', 'code' => Role::SUPER_ADMIN, 'description' => 'Full system owner'],
            ['name' => 'Admin', 'display_name' => 'Admin', 'code' => Role::ADMIN, 'description' => 'Operational manager'],
            ['name' => 'Principal', 'display_name' => 'Principal', 'code' => Role::PRINCIPAL, 'description' => 'Read-only academic monitor'],
            ['name' => 'Teacher', 'display_name' => 'Teacher', 'code' => Role::TEACHER, 'description' => 'Course and exam manager'],
            ['name' => 'Student', 'display_name' => 'Student', 'code' => Role::STUDENT, 'description' => 'Learner'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['code' => $role['code']], $role + ['is_active' => true]);
        }
    }
}
