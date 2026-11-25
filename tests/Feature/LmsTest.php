<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function user(string $role): User
    {
        return User::where('email', "{$role}@demo.test")->sole();
    }

    private function freshStudent(): User
    {
        return User::create(['name' => 'New', 'email' => 'new@example.com', 'password' => 'secret123', 'role' => 'student']);
    }

    public function test_catalog_renders_in_every_language_and_hides_drafts(): void
    {
        foreach (['en' => 'Laravel basics', 'ja' => 'Laravel 入門', 'vi' => 'Laravel cơ bản'] as $locale => $text) {
            $this->get(route('locale', $locale));
            $this->get('/')->assertOk()->assertSee($text)->assertDontSee('draft');
        }
        $this->get('/courses/vue-for-laravel-devs')->assertNotFound();
    }

    public function test_demo_logins_route_each_role(): void
    {
        $this->post(route('demo-login', 'student'))->assertRedirect(route('learning.dashboard'));
        $this->post(route('logout'));
        $this->post(route('demo-login', 'instructor'))->assertRedirect(route('admin.dashboard'));
        $this->post(route('logout'));
        $this->post(route('demo-login', 'hacker'))->assertNotFound();
    }

    public function test_full_learning_flow_reaches_100_percent(): void
    {
        $student = $this->freshStudent();
        $course = Course::where('slug', 'laravel-basics')->sole();
        $this->actingAs($student);

        $this->post(route('courses.enroll', $course))->assertRedirect(route('lessons.show', [$course, 1]));
        $this->post(route('courses.enroll', $course)); // double click
        $this->assertSame(1, Enrollment::where('user_id', $student->id)->count());

        foreach ($course->lessons as $lesson) {
            $this->get(route('lessons.show', [$course, $lesson->position]))->assertOk();
            $this->post(route('lessons.complete', [$course, $lesson->position]));
        }
        $this->assertSame(90, Enrollment::where('user_id', $student->id)->sole()->progress);

        $answers = $course->questions->mapWithKeys(fn (Question $q) => [$q->id => $q->correct_option])->all();
        $this->post(route('quiz.submit', $course), ['answers' => $answers])->assertSessionHas('status');

        $enrollment = Enrollment::where('user_id', $student->id)->sole();
        $this->assertSame(100, $enrollment->progress);
        $this->assertNotNull($enrollment->completed_at);
    }

    public function test_quiz_does_not_leak_answers_and_failing_does_not_complete(): void
    {
        $student = $this->freshStudent();
        $course = Course::where('slug', 'sql-performance')->sole();
        $this->actingAs($student)->post(route('courses.enroll', $course));

        $this->get(route('quiz.show', $course))->assertOk()->assertDontSee('correct_option');

        $wrong = $course->questions->mapWithKeys(fn ($q) => [$q->id => ($q->correct_option + 1) % count($q->options)])->all();
        $this->post(route('quiz.submit', $course), ['answers' => $wrong]);

        $this->assertNull(Enrollment::where('user_id', $student->id)->sole()->completed_at);
    }

    public function test_lessons_require_enrollment(): void
    {
        $this->actingAs($this->freshStudent())
            ->get(route('lessons.show', ['laravel-basics', 1]))->assertForbidden();
    }

    public function test_students_cannot_open_admin(): void
    {
        $this->actingAs($this->user('student'))->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_instructor_only_manages_own_courses(): void
    {
        $instructor = $this->user('instructor');
        $own = Course::where('instructor_id', $instructor->id)->first();
        $other = Course::where('instructor_id', '!=', $instructor->id)->first();

        $this->actingAs($instructor);
        $this->get(route('admin.courses.edit', $own))->assertOk();
        $this->get(route('admin.courses.edit', $other))->assertForbidden();
        $this->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_every_admin_screen_renders(): void
    {
        $this->actingAs($this->user('admin'));
        $course = Course::first();

        foreach ([route('admin.dashboard'), route('admin.courses.index'), route('admin.courses.create'),
            route('admin.courses.edit', $course), route('admin.courses.students', $course), route('admin.users.index')] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_instructor_builds_a_course_with_lessons_and_quiz(): void
    {
        $this->actingAs($this->user('instructor'));

        $this->post(route('admin.courses.store'), [
            'title' => ['en' => 'Testing in Laravel', 'ja' => 'Laravel のテスト'],
            'summary' => ['en' => 'Feature tests'], 'level' => 'beginner', 'color' => '#123456', 'pass_score' => 50, 'is_published' => '1',
        ])->assertRedirect();
        $course = Course::where('title->en', 'Testing in Laravel')->sole();

        $this->post(route('admin.lessons.store', $course), ['title' => ['en' => 'Intro'], 'body' => ['en' => '**Hello**'], 'duration_minutes' => 5])->assertRedirect();
        $this->post(route('admin.questions.store', $course), [
            'prompt' => ['en' => '2 + 2?'], 'options' => [['en' => '3'], ['en' => '4']], 'correct_option' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $course->lessons()->count());
        $this->assertSame(1, $course->questions()->sole()->correct_option);
        $this->get(route('courses.show', $course))->assertOk()->assertSee('Testing in Laravel');
    }

    public function test_catalog_cache_is_invalidated_on_edit(): void
    {
        $this->get('/')->assertSee('Laravel basics in one afternoon');
        $course = Course::where('slug', 'laravel-basics')->sole();
        $course->update(['title' => ['en' => 'Renamed course']]);

        $this->get('/')->assertSee('Renamed course');
    }

    public function test_catalog_cache_works_with_the_database_store(): void
    {
        config(['cache.default' => 'database']);

        foreach ([1, 2] as $pass) { // second pass reads from the cache
            $this->get('/')->assertOk()->assertSee('Laravel basics in one afternoon');
            $this->get('/courses/laravel-basics')->assertOk()->assertSee('Aiko Tanaka');
        }
    }

    public function test_lesson_markdown_is_escaped(): void
    {
        $course = Course::where('slug', 'laravel-basics')->sole();
        $course->lessons()->first()->update(['body' => ['en' => '<script>alert(1)</script>']]);

        $this->actingAs($this->user('admin'))
            ->get(route('lessons.show', [$course, 1]))->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_admin_changes_roles_but_not_demo_accounts(): void
    {
        $this->actingAs($this->user('admin'));
        $user = User::where('email', 'learner1@example.com')->sole();

        $this->patch(route('admin.users.update', $user), ['role' => 'instructor'])->assertRedirect();
        $this->assertSame('instructor', $user->fresh()->role);

        $this->patch(route('admin.users.update', $this->user('student')), ['role' => 'admin'])->assertStatus(422);
    }

    public function test_security_headers_and_custom_errors(): void
    {
        $this->get('/')->assertHeader('X-Frame-Options', 'DENY')->assertHeaderMissing('X-Powered-By');
        $this->get('/nope')->assertNotFound()->assertSee('Page not found');

        config(['app.debug' => false]);
        Route::get('/boom', fn () => throw new \RuntimeException('secret-internal-detail'))->middleware('web');
        $this->get('/boom')->assertStatus(500)->assertDontSee('secret-internal-detail');
    }

    public function test_performance_page_renders(): void
    {
        $this->get(route('performance'))->assertOk()->assertSee('locustfile.py');
    }
}
