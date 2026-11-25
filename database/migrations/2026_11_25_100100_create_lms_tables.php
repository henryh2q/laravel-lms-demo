<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('users');
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('summary');
            $table->string('level', 20)->default('beginner');
            $table->string('color', 7)->default('#1f5fbf');
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('pass_score')->default(70); // percent
            $table->timestamps();
            $table->index(['is_published', 'created_at']);
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->json('title');
            $table->json('body');
            $table->unsignedInteger('duration_minutes')->default(5);
            $table->timestamps();
            $table->unique(['course_id', 'position']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->json('prompt');
            $table->json('options'); // [{"en": "...", "ja": "...", "vi": "..."}, ...]
            $table->unsignedTinyInteger('correct_option');
            $table->timestamps();
            $table->index(['course_id', 'position']);
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('progress')->default(0); // percent, denormalised for fast listing
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'course_id']);
            $table->index(['course_id', 'completed_at']);
        });

        Schema::create('lesson_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();
            $table->unique(['user_id', 'lesson_id']);
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score');
            $table->unsignedInteger('total');
            $table->boolean('passed');
            $table->json('answers');
            $table->timestamps();
            $table->index(['course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        foreach (['quiz_attempts', 'lesson_completions', 'enrollments', 'questions', 'lessons', 'courses'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
