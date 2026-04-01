<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'photo',
        'phone',
        'address',
        'gender',
        'birth_date',
        'birth_place',
        'religion',
        'source_class_name',
        'parent_name',
        'parent_birth_year',
        'parent_education',
        'employment_status',
        'ptk_type',
        'preferred_language',
        'bio',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
