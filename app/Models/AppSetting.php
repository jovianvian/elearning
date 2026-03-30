<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppSetting extends Model
{
    protected $fillable = [
        'app_name', 'school_name', 'school_logo', 'school_favicon', 'primary_color', 'secondary_color', 'accent_color',
        'default_locale', 'supported_locales_json', 'footer_text', 'school_email', 'school_phone', 'school_address',
        'active_academic_year_id', 'active_semester_id',
    ];

    protected function casts(): array
    {
        return ['supported_locales_json' => 'array'];
    }

    public function activeAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'active_academic_year_id');
    }

    public function activeSemester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'active_semester_id');
    }
}
