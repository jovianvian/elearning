<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class LearningMaterial extends Model
{
    public const TYPE_TEXT = 'text';
    public const TYPE_FILE = 'file';
    public const TYPE_LINK = 'link';
    public const TYPE_VIDEO = 'video';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'content',
        'type',
        'external_url',
        'file_path',
        'file_name',
        'sort_order',
        'is_published',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(StudentMaterialProgress::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return Storage::disk('public')->url($this->file_path);
    }

    public static function availableTypes(): array
    {
        return [
            self::TYPE_TEXT,
            self::TYPE_FILE,
            self::TYPE_LINK,
            self::TYPE_VIDEO,
        ];
    }
}

