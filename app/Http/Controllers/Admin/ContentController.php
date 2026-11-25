<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContentController extends Controller
{
    public function storeLesson(Request $request, Course $course)
    {
        Gate::authorize('manage-course', $course);
        $course->lessons()->create([...$this->lessonData($request), 'position' => (int) $course->lessons()->max('position') + 1]);

        return $this->back($course, 'Lesson added.');
    }

    public function updateLesson(Request $request, Lesson $lesson)
    {
        Gate::authorize('manage-course', $lesson->course);
        $lesson->update($this->lessonData($request));

        return $this->back($lesson->course, 'Lesson saved.');
    }

    public function destroyLesson(Lesson $lesson)
    {
        Gate::authorize('manage-course', $lesson->course);
        $course = $lesson->course;
        $lesson->delete();

        // Keep positions contiguous so lesson URLs stay 1..n.
        $course->lessons()->get()->each(fn (Lesson $l, int $i) => $l->update(['position' => $i + 1]));

        return $this->back($course, 'Lesson deleted.');
    }

    public function storeQuestion(Request $request, Course $course)
    {
        Gate::authorize('manage-course', $course);
        $course->questions()->create([...$this->questionData($request), 'position' => (int) $course->questions()->max('position') + 1]);

        return $this->back($course, 'Question added.');
    }

    public function updateQuestion(Request $request, Question $question)
    {
        $course = Course::findOrFail($question->course_id);
        Gate::authorize('manage-course', $course);
        $question->update($this->questionData($request));

        return $this->back($course, 'Question saved.');
    }

    public function destroyQuestion(Question $question)
    {
        $course = Course::findOrFail($question->course_id);
        Gate::authorize('manage-course', $course);
        $question->delete();

        return $this->back($course, 'Question deleted.');
    }

    private function lessonData(Request $request): array
    {
        $data = $request->validate([
            'title.en' => ['required', 'string', 'max:160'], 'title.ja' => ['nullable', 'string', 'max:160'], 'title.vi' => ['nullable', 'string', 'max:160'],
            'body.en' => ['required', 'string', 'max:20000'], 'body.ja' => ['nullable', 'string', 'max:20000'], 'body.vi' => ['nullable', 'string', 'max:20000'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
        ]);

        return [...$data, 'title' => array_filter($data['title']), 'body' => array_filter($data['body'])];
    }

    private function questionData(Request $request): array
    {
        $data = $request->validate([
            'prompt.en' => ['required', 'string', 'max:500'], 'prompt.ja' => ['nullable', 'string', 'max:500'], 'prompt.vi' => ['nullable', 'string', 'max:500'],
            'options' => ['required', 'array', 'min:2', 'max:6'],
            'options.*.en' => ['required', 'string', 'max:200'],
            'options.*.ja' => ['nullable', 'string', 'max:200'],
            'options.*.vi' => ['nullable', 'string', 'max:200'],
            'correct_option' => ['required', 'integer', 'min:0', 'lt:'.count((array) $request->input('options'))],
        ]);

        return [
            'prompt' => array_filter($data['prompt']),
            'options' => array_values(array_map('array_filter', $data['options'])),
            'correct_option' => (int) $data['correct_option'],
        ];
    }

    private function back(Course $course, string $status)
    {
        return redirect()->route('admin.courses.edit', $course)->with('status', $status);
    }
}
