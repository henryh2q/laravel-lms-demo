<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LearningService
{
    public function enroll(User $user, Course $course): Enrollment
    {
        // firstOrCreate + unique index: double clicks never create two enrollments.
        return Enrollment::firstOrCreate(['user_id' => $user->id, 'course_id' => $course->id]);
    }

    public function enrollment(User $user, Course $course): ?Enrollment
    {
        return Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->first();
    }

    /** @return list<int> */
    public function completedLessonIds(User $user, Course $course): array
    {
        return DB::table('lesson_completions')
            ->join('lessons', 'lessons.id', '=', 'lesson_completions.lesson_id')
            ->where('lesson_completions.user_id', $user->id)
            ->where('lessons.course_id', $course->id)
            ->pluck('lessons.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function completeLesson(User $user, Lesson $lesson): Enrollment
    {
        $enrollment = $this->enrollment($user, $lesson->course)
            ?? throw ValidationException::withMessages(['course' => __('Enroll in the course first.')]);

        DB::table('lesson_completions')->insertOrIgnore([
            'user_id' => $user->id, 'lesson_id' => $lesson->id, 'created_at' => now(),
        ]);

        return $this->refreshProgress($enrollment);
    }

    /**
     * Grade server-side: the correct answers never leave the server.
     *
     * @param  array<int|string, int|string>  $answers  question id => chosen option index
     */
    public function submitQuiz(User $user, Course $course, array $answers): QuizAttempt
    {
        $enrollment = $this->enrollment($user, $course)
            ?? throw ValidationException::withMessages(['course' => __('Enroll in the course first.')]);

        $questions = $course->questions()->get(['id', 'correct_option']);
        $score = $questions->filter(fn ($q) => isset($answers[$q->id]) && (int) $answers[$q->id] === $q->correct_option)->count();
        $total = $questions->count();

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'score' => $score,
            'total' => $total,
            'passed' => $total > 0 && $score * 100 >= $course->pass_score * $total,
            'answers' => $questions->mapWithKeys(fn ($q) => [$q->id => isset($answers[$q->id]) ? (int) $answers[$q->id] : null])->all(),
        ]);

        $this->refreshProgress($enrollment);

        return $attempt;
    }

    /** Progress = lessons done (90%) + passing the quiz (10%). Completed when both are done. */
    public function refreshProgress(Enrollment $enrollment): Enrollment
    {
        $course = $enrollment->course;
        $user = $enrollment->user;

        $lessonCount = $course->lessons()->count();
        $done = count($this->completedLessonIds($user, $course));
        $hasQuiz = $course->questions()->exists();
        $passed = ! $hasQuiz || QuizAttempt::where('user_id', $user->id)->where('course_id', $course->id)->where('passed', true)->exists();

        $lessonShare = $lessonCount ? $done / $lessonCount : 1;
        $progress = (int) floor($hasQuiz ? $lessonShare * 90 + ($passed ? 10 : 0) : $lessonShare * 100);
        $complete = $lessonShare >= 1 && $passed;

        $enrollment->update([
            'progress' => $complete ? 100 : min(99, $progress),
            'completed_at' => $complete ? ($enrollment->completed_at ?? now()) : null,
        ]);

        return $enrollment;
    }
}
