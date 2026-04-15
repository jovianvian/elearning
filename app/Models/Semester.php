<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    protected $fillable = ['academic_year_id', 'name', 'code', 'start_date', 'end_date', 'is_active'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'is_active' => 'boolean'];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function studentBills(): HasMany
    {
        return $this->hasMany(StudentBill::class);
    }
}
