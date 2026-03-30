<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        $accounts = [
            [
                'name' => 'Admin Sekolah',
                'email' => 'admin@edusasana.sch.id',
                'role' => Role::ADMIN,
            ],
            [
                'name' => 'Guru Matematika',
                'email' => 'guru@edusasana.sch.id',
                'role' => Role::GURU,
            ],
            [
                'name' => 'Siswa Demo',
                'email' => 'siswa@edusasana.sch.id',
                'role' => Role::SISWA,
            ],
        ];

        foreach ($accounts as $account) {
            $role = Role::query()->where('name', $account['role'])->firstOrFail();

            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'role_id' => $role->id,
                    'password' => $defaultPassword,
                ]
            );
        }
    }
}
