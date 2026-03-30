<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public const ADMIN = 'admin';
    public const GURU = 'guru';
    public const SISWA = 'siswa';

    protected $fillable = [
        'name',
        'display_name',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
