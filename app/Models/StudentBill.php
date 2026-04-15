<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentBill extends Model
{
    public const STATUS_UNPAID = 'unpaid';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester_id',
        'total_amount',
        'paid_amount',
        'status',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class)->orderBy('month_number');
    }
}

