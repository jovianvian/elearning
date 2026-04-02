<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ExamAccessService
{
    public function canManageCourseExam(User $user, Course $course): bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN)) {
            return true;
        }

        if (! $user->hasRole(Role::TEACHER)) {
            return false;
        }

        if ($course->teachers()->where('users.id', $user->id)->exists()) {
            return true;
        }

        // Fallback: some schools only assign teacher at subject level.
        return DB::table('subject_teachers')
            ->where('teacher_id', $user->id)
            ->where('subject_id', $course->subject_id)
            ->where('is_active', 1)
            ->exists();
    }

    public function canManageExam(User $user, Exam $exam): bool
    {
        return $this->canManageCourseExam($user, $exam->course);
    }

    public function canViewExam(User $user, Exam $exam): bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL)) {
            return true;
        }

        if ($user->hasRole(Role::TEACHER)) {
            return $exam->course->teachers()->where('users.id', $user->id)->exists();
        }

        if ($user->hasRole(Role::STUDENT)) {
            return $exam->course->students()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    public function canTakeExam(User $user, Exam $exam): bool
    {
        return $user->hasRole(Role::STUDENT)
            && $exam->is_published
            && $exam->course->students()->where('users.id', $user->id)->exists();
    }

    public function canViewAttempt(User $user, ExamAttempt $attempt): bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN)) {
            return true;
        }

        if ($user->hasRole(Role::TEACHER)) {
            return $attempt->exam->course->teachers()->where('users.id', $user->id)->exists();
        }

        if ($user->hasRole(Role::STUDENT)) {
            return (int) $attempt->student_id === (int) $user->id;
        }

        if ($user->hasRole(Role::PRINCIPAL)) {
            return true;
        }

        return false;
    }
}
