<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPublicationLog extends Model
{
    protected $fillable = ['exam_id', 'published_by', 'published_at', 'note'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }
}
