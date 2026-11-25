<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\LearningService;
use App\Support\CatalogCache;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $level = in_array($request->query('level'), ['beginner', 'intermediate', 'advanced'], true) ? $request->query('level') : null;

        $courses = CatalogCache::remember('index:'.($level ?? 'all'), fn () => Course::published()
            ->when($level, fn ($q) => $q->where('level', $level))
            ->with('instructor:id,name')
            ->withCount(['lessons', 'enrollments'])
            ->withSum('lessons as minutes', 'duration_minutes')
            ->latest()
            ->get());

        return view('catalog.index', compact('courses', 'level'));
    }

    public function show(Request $request, Course $course, LearningService $learning)
    {
        abort_unless($course->is_published || $request->user()?->can('manage-course', $course), 404);

        $course = CatalogCache::remember("course:{$course->id}", fn () => $course->load('instructor:id,name', 'lessons:id,course_id,position,title,duration_minutes')->loadCount(['questions', 'enrollments']));

        $enrollment = $request->user() ? $learning->enrollment($request->user(), $course) : null;
        $done = $enrollment ? $learning->completedLessonIds($request->user(), $course) : [];

        return view('catalog.show', compact('course', 'enrollment', 'done'));
    }
}
