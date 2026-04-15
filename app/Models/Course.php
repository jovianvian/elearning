<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subject_id', 'class_id', 'academic_year_id', 'semester_id', 'title', 'slug', 'description', 'is_published', 'created_by',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function academicYear(): BelongsTo { return $this->belongsTo(AcademicYear::class); }
    public function semester(): BelongsTo { return $this->belongsTo(Semester::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_teachers', 'course_id', 'teacher_id')
            ->withPivot('is_main_teacher')->withTimestamps();
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_students', 'course_id', 'student_id')
            ->withPivot('enrolled_at')->withTimestamps();
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function learningMaterials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class)->orderBy('sort_order')->orderBy('id');
    }
}
