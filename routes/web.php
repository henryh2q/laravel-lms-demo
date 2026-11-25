<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PerformanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/courses/{course}', [CatalogController::class, 'show'])->name('courses.show');
Route::get('/performance', PerformanceController::class)->name('performance');
Route::get('/lang/{locale}', LocaleController::class)->name('locale');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:10,1');
    Route::post('/demo-login/{role}', [AuthController::class, 'demo'])->middleware('throttle:20,1')->name('demo-login');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegistration'])->middleware('throttle:5,1');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/my-learning', [LearnController::class, 'dashboard'])->name('learning.dashboard');
    Route::post('/courses/{course}/enroll', [LearnController::class, 'enroll'])->name('courses.enroll');
    Route::get('/courses/{course}/lessons/{position}', [LearnController::class, 'lesson'])->whereNumber('position')->name('lessons.show');
    Route::post('/courses/{course}/lessons/{position}/complete', [LearnController::class, 'complete'])->whereNumber('position')->name('lessons.complete');
    Route::get('/courses/{course}/quiz', [LearnController::class, 'quiz'])->name('quiz.show');
    Route::post('/courses/{course}/quiz', [LearnController::class, 'submitQuiz'])->middleware('throttle:20,1')->name('quiz.submit');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'staff'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');
    Route::resource('courses', Admin\CourseController::class)->except('show');
    Route::post('courses/{course}/lessons', [Admin\ContentController::class, 'storeLesson'])->name('lessons.store');
    Route::put('lessons/{lesson}', [Admin\ContentController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('lessons/{lesson}', [Admin\ContentController::class, 'destroyLesson'])->name('lessons.destroy');
    Route::post('courses/{course}/questions', [Admin\ContentController::class, 'storeQuestion'])->name('questions.store');
    Route::put('questions/{question}', [Admin\ContentController::class, 'updateQuestion'])->name('questions.update');
    Route::delete('questions/{question}', [Admin\ContentController::class, 'destroyQuestion'])->name('questions.destroy');
    Route::get('courses/{course}/students', [Admin\CourseController::class, 'students'])->name('courses.students');

    Route::middleware('staff:admin')->group(function () {
        Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::patch('users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
    });
});
