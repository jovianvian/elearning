<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $role = auth()->user()?->role?->name;

        return match ($role) {
            Role::ADMIN => redirect()->route('dashboard.admin'),
            Role::GURU => redirect()->route('dashboard.guru'),
            Role::SISWA => redirect()->route('dashboard.siswa'),
            default => redirect()->route('login'),
        };
    }

    public function admin(): View
    {
        return view('dashboard.admin', [
            'stats' => [
                'total_users' => User::count(),
                'total_guru' => User::whereHas('role', fn ($query) => $query->where('name', Role::GURU))->count(),
                'total_siswa' => User::whereHas('role', fn ($query) => $query->where('name', Role::SISWA))->count(),
                'total_kelas' => SchoolClass::count(),
                'total_mapel' => Subject::count(),
                'total_materi' => Material::count(),
            ],
        ]);
    }

    public function guru(): View
    {
        $user = auth()->user();

        return view('dashboard.guru', [
            'stats' => [
                'mapel_diampu' => Subject::where('teacher_id', $user->id)->count(),
                'materi_dibuat' => Material::where('created_by', $user->id)->count(),
                'kelas_terlibat' => SchoolClass::where('homeroom_teacher_id', $user->id)->count(),
            ],
        ]);
    }

    public function siswa(): View
    {
        return view('dashboard.siswa', [
            'stats' => [
                'materi_tersedia' => Material::whereNotNull('published_at')->count(),
                'mapel_aktif' => Subject::count(),
                'kelas_aktif' => SchoolClass::count(),
            ],
        ]);
    }
}
