<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    protected $fillable = ['user_id', 'course_id', 'score', 'total', 'passed', 'answers'];

    protected $casts = ['answers' => 'array', 'passed' => 'boolean'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function percent(): int
    {
        return $this->total ? (int) round($this->score * 100 / $this->total) : 0;
    }
}
