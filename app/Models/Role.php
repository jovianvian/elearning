<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];

    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN = 'admin';
    public const PRINCIPAL = 'principal';
    public const TEACHER = 'teacher';
    public const STUDENT = 'student';
    public const GURU = 'teacher';
    public const SISWA = 'student';

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
