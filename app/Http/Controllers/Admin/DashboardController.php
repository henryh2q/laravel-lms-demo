<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $courseIds = Course::manageableBy($user)->pluck('id');

        $enrollments = Enrollment::whereIn('course_id', $courseIds);

        return view('admin.dashboard', [
            'stats' => [
                'courses' => $courseIds->count(),
                'students' => (clone $enrollments)->distinct('user_id')->count('user_id'),
                'enrollments' => (clone $enrollments)->count(),
                'completion' => (int) round(((clone $enrollments)->whereNotNull('completed_at')->count() / max(1, (clone $enrollments)->count())) * 100),
                'avg_score' => (int) round((float) QuizAttempt::whereIn('course_id', $courseIds)->selectRaw('AVG(score * 100.0 / NULLIF(total, 0)) as a')->value('a')),
            ],
            'courses' => Course::manageableBy($user)
                ->withCount(['lessons', 'enrollments', 'enrollments as completed_count' => fn ($q) => $q->whereNotNull('completed_at')])
                ->withAvg('enrollments as avg_progress', 'progress')
                ->latest()->get(),
            'recentAttempts' => QuizAttempt::whereIn('course_id', $courseIds)->with(['user:id,name', 'course:id,slug,title'])->latest()->limit(8)->get(),
            'userCount' => $user->isAdmin() ? User::count() : null,
        ]);
    }
}
