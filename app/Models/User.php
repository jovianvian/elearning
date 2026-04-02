<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id',
        'school_class_id',
        'full_name',
        'username',
        'email',
        'nis',
        'nip',
        'password',
        'is_active',
        'must_change_password',
        'last_login_at',
        'blocked_at',
        'blocked_reason',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
            'blocked_at' => 'datetime',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function classAssignments(): HasMany
    {
        return $this->hasMany(ClassStudent::class, 'student_id');
    }

    public function taughtSubjects(): HasMany
    {
        return $this->hasMany(SubjectTeacher::class, 'teacher_id');
    }

    public function assignedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_teachers', 'teacher_id', 'course_id')
            ->withPivot('is_main_teacher')
            ->withTimestamps();
    }

    public function studentCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_students', 'student_id', 'course_id')
            ->withPivot('enrolled_at')
            ->withTimestamps();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function hasRole(string ...$codes): bool
    {
        $roleCode = $this->normalizeRoleCode($this->role?->code);
        if ($roleCode === null) {
            return false;
        }

        $normalizedCodes = array_map(fn (string $code): string => $this->normalizeRoleCode($code) ?? $code, $codes);

        return in_array($roleCode, $normalizedCodes, true);
    }

    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    private function normalizeRoleCode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $normalized = strtolower(str_replace('-', '_', trim($code)));
        if ($normalized === 'superadmin') {
            return Role::SUPER_ADMIN;
        }

        return $normalized;
    }
}
