@extends('layouts.admin')
@section('title', $course->exists ? 'Edit course' : 'New course')
@section('content')
<a href="{{ route('admin.courses.index') }}" class="text-sm text-slate-500 hover:underline">← Courses</a>
<div class="mb-6 mt-2 flex flex-wrap items-center gap-3">
    <h1 class="text-2xl font-bold">{{ $course->exists ? $course->translate('title', 'en') : 'New course' }}</h1>
    @if ($course->exists)<a href="{{ route('courses.show', $course) }}" target="_blank" class="text-sm text-brand-600 hover:underline">Preview ↗</a><a href="{{ route('admin.courses.students', $course) }}" class="text-sm text-brand-600 hover:underline">Students</a>@endif
</div>

<form method="POST" action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}" class="card space-y-4 p-6">
    @csrf
    @if ($course->exists) @method('PUT') @endif
    @include('admin.courses._translations', ['fields' => ['title' => ['input', 'Title'], 'summary' => ['textarea', 'Summary']], 'values' => ['title' => old('title', $course->title), 'summary' => old('summary', $course->summary)]])
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div><label class="label" for="level">Level</label><select id="level" name="level" class="input">@foreach (['beginner', 'intermediate', 'advanced'] as $l)<option @selected(old('level', $course->level) === $l)>{{ $l }}</option>@endforeach</select></div>
        <div><label class="label" for="pass_score">Pass score (%)</label><input id="pass_score" name="pass_score" type="number" min="1" max="100" value="{{ old('pass_score', $course->pass_score) }}" class="input"></div>
        <div><label class="label" for="color">Colour</label><input id="color" name="color" type="color" value="{{ old('color', $course->color) }}" class="input h-10 p-1"></div>
        <div><label class="label" for="instructor_id">Instructor</label><select id="instructor_id" name="instructor_id" class="input">@foreach ($instructors as $i)<option value="{{ $i->id }}" @selected(old('instructor_id', $course->instructor_id ?? auth()->id()) == $i->id)>{{ $i->name }}</option>@endforeach</select></div>
        <label class="flex items-center gap-2 self-end pb-2 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $course->is_published))> Published</label>
    </div>
    <button class="btn-primary">Save course</button>
</form>

@if ($course->exists)
    <h2 class="mb-3 mt-10 text-lg font-semibold">Lessons</h2>
    <div class="space-y-3">
        @foreach ($course->lessons as $lesson)
            <details class="card">
                <summary class="flex cursor-pointer items-center gap-3 p-4"><span class="badge bg-slate-100">{{ $lesson->position }}</span><span class="flex-1 font-medium">{{ $lesson->translate('title', 'en') }}</span><span class="text-xs text-slate-500">{{ $lesson->duration_minutes }} min · {{ strtoupper(implode(' ', array_keys($lesson->title))) }}</span></summary>
                <form method="POST" action="{{ route('admin.lessons.update', $lesson) }}" class="space-y-3 border-t border-slate-100 p-4">
                    @csrf @method('PUT')
                    @include('admin.courses._translations', ['fields' => ['title' => ['input', 'Title'], 'body' => ['textarea', 'Body (Markdown)']], 'values' => ['title' => $lesson->title, 'body' => $lesson->body], 'rows' => 6])
                    <div class="flex items-end gap-3"><div><label class="label">Minutes</label><input name="duration_minutes" type="number" min="1" value="{{ $lesson->duration_minutes }}" class="input w-24"></div><button class="btn-primary">Save lesson</button></div>
                </form>
                <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" class="px-4 pb-4" data-confirm="Delete this lesson?">@csrf @method('DELETE')<button class="text-sm text-red-600 hover:underline">Delete lesson</button></form>
            </details>
        @endforeach
        <details class="card border-dashed" @if ($course->lessons->isEmpty()) open @endif>
            <summary class="cursor-pointer p-4 font-medium text-brand-600">+ Add lesson</summary>
            <form method="POST" action="{{ route('admin.lessons.store', $course) }}" class="space-y-3 border-t border-slate-100 p-4">
                @csrf
                @include('admin.courses._translations', ['fields' => ['title' => ['input', 'Title'], 'body' => ['textarea', 'Body (Markdown)']], 'values' => [], 'rows' => 6])
                <div class="flex items-end gap-3"><div><label class="label">Minutes</label><input name="duration_minutes" type="number" min="1" value="10" class="input w-24"></div><button class="btn-primary">Add lesson</button></div>
            </form>
        </details>
    </div>

    <h2 class="mb-3 mt-10 text-lg font-semibold">Quiz questions</h2>
    <div class="space-y-3">
        @foreach ($course->questions as $question)
            <details class="card">
                <summary class="flex cursor-pointer items-center gap-3 p-4"><span class="badge bg-slate-100">Q{{ $loop->iteration }}</span><span class="flex-1 font-medium">{{ $question->translate('prompt', 'en') }}</span><span class="text-xs text-emerald-700">✓ {{ $question->translatedOptions('en')[$question->correct_option] ?? '' }}</span></summary>
                @include('admin.courses._question', ['question' => $question, 'action' => route('admin.questions.update', $question), 'method' => 'PUT'])
                <form method="POST" action="{{ route('admin.questions.destroy', $question) }}" class="px-4 pb-4" data-confirm="Delete this question?">@csrf @method('DELETE')<button class="text-sm text-red-600 hover:underline">Delete question</button></form>
            </details>
        @endforeach
        <details class="card border-dashed">
            <summary class="cursor-pointer p-4 font-medium text-brand-600">+ Add question</summary>
            @include('admin.courses._question', ['question' => new \App\Models\Question(['options' => [[], [], [], []], 'correct_option' => 0]), 'action' => route('admin.questions.store', $course), 'method' => 'POST'])
        </details>
    </div>

    <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="mt-10" data-confirm="Delete this course and all its content?">@csrf @method('DELETE')<button class="text-sm text-red-600 hover:underline">Delete course</button></form>
@endif
@endsection
