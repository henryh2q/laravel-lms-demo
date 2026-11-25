<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Services\LearningService;
use Illuminate\Http\Request;

class LearnController extends Controller
{
    public function __construct(private readonly LearningService $learning) {}

    public function dashboard(Request $request)
    {
        $enrollments = $request->user()->enrollments()
            ->with(['course' => fn ($q) => $q->withCount('lessons')])
            ->latest('updated_at')
            ->get();

        $attempts = QuizAttempt::where('user_id', $request->user()->id)->with('course:id,slug,title')->latest()->limit(5)->get();

        return view('learn.dashboard', compact('enrollments', 'attempts'));
    }

    public function enroll(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);

        $this->learning->enroll($request->user(), $course);

        return redirect()->route('lessons.show', [$course, 1]);
    }

    public function lesson(Request $request, Course $course, int $position)
    {
        $lesson = $this->lessonOrFail($request, $course, $position);
        $lessons = $course->lessons()->get(['id', 'course_id', 'position', 'title', 'duration_minutes']);
        $done = $this->learning->completedLessonIds($request->user(), $course);

        return view('learn.lesson', compact('course', 'lesson', 'lessons', 'done'));
    }

    public function complete(Request $request, Course $course, int $position)
    {
        $lesson = $this->lessonOrFail($request, $course, $position);
        $this->learning->completeLesson($request->user(), $lesson);

        $next = $course->lessons()->where('position', '>', $position)->value('position');

        return $next
            ? redirect()->route('lessons.show', [$course, $next])
            : redirect()->route('quiz.show', $course);
    }

    public function quiz(Request $request, Course $course)
    {
        abort_unless($this->learning->enrollment($request->user(), $course), 403);

        $questions = $course->questions()->get(['id', 'course_id', 'position', 'prompt', 'options']);
        $last = QuizAttempt::where('user_id', $request->user()->id)->where('course_id', $course->id)->latest('id')->first();

        return view('learn.quiz', compact('course', 'questions', 'last'));
    }

    public function submitQuiz(Request $request, Course $course)
    {
        $data = $request->validate(['answers' => ['required', 'array'], 'answers.*' => ['integer', 'min:0', 'max:9']]);

        $attempt = $this->learning->submitQuiz($request->user(), $course, $data['answers']);

        return redirect()->route('quiz.show', $course)->with('status', $attempt->passed
            ? __('Passed with :score%!', ['score' => $attempt->percent()])
            : __('Score :score%. You need :pass% to pass, try again.', ['score' => $attempt->percent(), 'pass' => $course->pass_score]));
    }

    private function lessonOrFail(Request $request, Course $course, int $position): Lesson
    {
        abort_unless($this->learning->enrollment($request->user(), $course) || $request->user()->can('manage-course', $course), 403);

        return $course->lessons()->where('position', $position)->firstOrFail();
    }
}
