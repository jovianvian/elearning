<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => Role::ADMIN, 'display_name' => 'Administrator'],
            ['name' => Role::GURU, 'display_name' => 'Guru'],
            ['name' => Role::SISWA, 'display_name' => 'Siswa'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
