<?php

namespace App\Services;

use App\Models\QuestionBank;
use App\Models\Role;
use App\Models\SubjectTeacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class QuestionAccessService
{
    public function scopeAccessibleBanks(Builder $query, User $user): Builder
    {
        if ($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL)) {
            return $query;
        }

        if ($user->hasRole(Role::TEACHER)) {
            $subjectIds = $this->teacherSubjectIds($user);

            return $query->whereIn('subject_id', $subjectIds)
                ->where(static function (Builder $q) use ($user): void {
                    $q->where('visibility', QuestionBank::VISIBILITY_SUBJECT_SHARED)
                        ->orWhere('created_by', $user->id);
                });
        }

        return $query->whereRaw('1 = 0');
    }

    public function canCreateBankForSubject(User $user, int $subjectId): bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN)) {
            return true;
        }

        if (! $user->hasRole(Role::TEACHER)) {
            return false;
        }

        return in_array($subjectId, $this->teacherSubjectIds($user), true);
    }

    public function canViewBank(User $user, QuestionBank $bank): bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::PRINCIPAL)) {
            return true;
        }

        if (! $user->hasRole(Role::TEACHER)) {
            return false;
        }

        if (! in_array((int) $bank->subject_id, $this->teacherSubjectIds($user), true)) {
            return false;
        }

        return $bank->visibility === QuestionBank::VISIBILITY_SUBJECT_SHARED || (int) $bank->created_by === (int) $user->id;
    }

    public function canManageBank(User $user, QuestionBank $bank): bool
    {
        if ($user->hasRole(Role::SUPER_ADMIN, Role::ADMIN)) {
            return true;
        }

        if (! $user->hasRole(Role::TEACHER)) {
            return false;
        }

        if (! in_array((int) $bank->subject_id, $this->teacherSubjectIds($user), true)) {
            return false;
        }

        if ((int) $bank->created_by === (int) $user->id) {
            return true;
        }

        return $bank->visibility === QuestionBank::VISIBILITY_SUBJECT_SHARED;
    }

    public function teacherSubjectIds(User $user): array
    {
        return SubjectTeacher::query()
            ->where('teacher_id', $user->id)
            ->where('is_active', true)
            ->distinct()
            ->pluck('subject_id')
            ->map(static fn ($id) => (int) $id)
            ->all();
    }
}

