<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasTranslations;

    protected $fillable = ['instructor_id', 'slug', 'title', 'summary', 'level', 'color', 'is_published', 'pass_score'];

    protected $casts = ['title' => 'array', 'summary' => 'array', 'is_published' => 'boolean', 'pass_score' => 'integer'];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('position');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /** Admins manage everything, instructors only their own courses. */
    public function scopeManageableBy(Builder $query, User $user): Builder
    {
        return $user->isAdmin() ? $query : $query->where('instructor_id', $user->id);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
