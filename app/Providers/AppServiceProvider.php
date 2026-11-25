<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\User;
use App\Support\CatalogCache;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Paginator::useTailwind();

        Gate::define('manage-course', fn (User $user, Course $course) => $user->isAdmin() || $user->id === $course->instructor_id);

        // The public catalog is cached; any content change bumps the cache version.
        foreach ([Course::class, Lesson::class, Question::class] as $model) {
            $model::saved(fn () => CatalogCache::bump());
            $model::deleted(fn () => CatalogCache::bump());
        }
    }
}
