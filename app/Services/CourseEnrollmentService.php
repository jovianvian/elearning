<?php

namespace App\Services;

use App\Models\Course;

class CourseEnrollmentService
{
    public function syncStudentsFromClass(Course $course): void
    {
        $studentIds = $course->schoolClass
            ->students()
            ->wherePivot('academic_year_id', $course->academic_year_id)
            ->pluck('users.id')
            ->all();

        $payload = [];
        foreach ($studentIds as $studentId) {
            $payload[$studentId] = ['enrolled_at' => now()];
        }

        $course->students()->sync($payload);
    }
}
