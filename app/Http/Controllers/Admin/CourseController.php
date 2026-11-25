<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::manageableBy($request->user())->with('instructor:id,name')->withCount(['lessons', 'questions', 'enrollments'])->latest()->paginate(20);

        return view('admin.courses.index', compact('courses'));
    }

    public function create(Request $request)
    {
        return view('admin.courses.form', ['course' => new Course(['level' => 'beginner', 'pass_score' => 70, 'color' => '#e2603a']), 'instructors' => $this->instructors($request)]);
    }

    public function store(Request $request)
    {
        $course = Course::create([...$this->validated($request), 'slug' => Str::slug($request->input('title.en')).'-'.Str::lower(Str::random(4))]);

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Course created. Add lessons and quiz questions below.');
    }

    public function edit(Request $request, Course $course)
    {
        Gate::authorize('manage-course', $course);

        return view('admin.courses.form', [
            'course' => $course->load('lessons', 'questions'),
            'instructors' => $this->instructors($request),
        ]);
    }

    public function update(Request $request, Course $course)
    {
        Gate::authorize('manage-course', $course);
        $course->update($this->validated($request));

        return redirect()->route('admin.courses.edit', $course)->with('status', 'Course saved.');
    }

    public function destroy(Course $course)
    {
        Gate::authorize('manage-course', $course);
        $course->delete();

        return redirect()->route('admin.courses.index')->with('status', 'Course deleted.');
    }

    public function students(Course $course)
    {
        Gate::authorize('manage-course', $course);

        $enrollments = $course->enrollments()->with('user:id,name,email')->orderByDesc('progress')->paginate(30);

        return view('admin.courses.students', compact('course', 'enrollments'));
    }

    private function instructors(Request $request)
    {
        return $request->user()->isAdmin()
            ? User::whereIn('role', ['admin', 'instructor'])->orderBy('name')->get(['id', 'name'])
            : collect([$request->user()]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title.en' => ['required', 'string', 'max:160'],
            'title.ja' => ['nullable', 'string', 'max:160'],
            'title.vi' => ['nullable', 'string', 'max:160'],
            'summary.en' => ['required', 'string', 'max:2000'],
            'summary.ja' => ['nullable', 'string', 'max:2000'],
            'summary.vi' => ['nullable', 'string', 'max:2000'],
            'level' => ['required', 'in:beginner,intermediate,advanced'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'pass_score' => ['required', 'integer', 'min:1', 'max:100'],
            // Only Unsplash CDN photos, which the CSP allows; credit is "Name|username".
            'image_url' => ['nullable', 'url', 'starts_with:https://images.unsplash.com/photo-', 'max:255'],
            'image_credit' => ['nullable', 'string', 'max:120', 'regex:/^[^|]+\|[A-Za-z0-9_\-]+$/'],
            'instructor_id' => ['nullable', 'exists:users,id'],
        ]);

        return [
            ...$data,
            'title' => array_filter($data['title']),
            'summary' => array_filter($data['summary']),
            // Instructors can only own their own courses.
            'instructor_id' => $request->user()->isAdmin() ? ($data['instructor_id'] ?? $request->user()->id) : $request->user()->id,
            'is_published' => $request->boolean('is_published'),
        ];
    }
}
