<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMaterialProgress extends Model
{
    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'student_material_progress';

    protected $fillable = [
        'learning_material_id',
        'student_id',
        'status',
        'first_opened_at',
        'last_accessed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'first_opened_at' => 'datetime',
            'last_accessed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(LearningMaterial::class, 'learning_material_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}

