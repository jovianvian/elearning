<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseTeacher extends Model
{
    protected $fillable = ['course_id', 'teacher_id', 'is_main_teacher'];

    protected function casts(): array
    {
        return ['is_main_teacher' => 'boolean'];
    }
}
