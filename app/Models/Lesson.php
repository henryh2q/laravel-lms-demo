<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasTranslations;

    protected $fillable = ['course_id', 'position', 'title', 'body', 'duration_minutes'];

    protected $casts = ['title' => 'array', 'body' => 'array'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
