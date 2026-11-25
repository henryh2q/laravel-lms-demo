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

    protected $fillable = ['instructor_id', 'slug', 'title', 'summary', 'level', 'color', 'image_url', 'image_credit', 'is_published', 'pass_score'];

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

    /** Unsplash photos are resized by their CDN; other URLs are used as they are. */
    public function imageSrc(int $width = 800, int $height = 450): ?string
    {
        if (! $this->image_url) {
            return null;
        }

        return str_starts_with($this->image_url, 'https://images.unsplash.com/')
            ? $this->image_url."?w={$width}&h={$height}&fit=crop&auto=format&q=75"
            : $this->image_url;
    }

    /** @return array{name: string, url: string}|null */
    public function imageCredit(): ?array
    {
        if (! $this->image_credit) {
            return null;
        }

        [$name, $username] = array_pad(explode('|', $this->image_credit, 2), 2, null);

        return ['name' => $name, 'url' => 'https://unsplash.com/@'.$username.'?utm_source=manabi_lms_demo&utm_medium=referral'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
