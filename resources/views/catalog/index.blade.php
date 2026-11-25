@extends('layouts.app')
@section('title', __('Courses'))
@section('content')
<section class="mb-8 rounded-2xl bg-gradient-to-br from-brand-600 to-slate-900 px-6 py-10 text-white">
    <h1 class="text-3xl font-bold">{{ __('Learn at your own pace') }}</h1>
    <p class="mt-2 max-w-2xl text-slate-200">{{ __('A working LMS demo: enroll in a course, finish lessons, pass the quiz and track progress. Instructors and admins manage content from their own panel.') }}</p>
    @guest<a href="{{ route('login') }}" class="btn mt-5 bg-white text-slate-900 hover:bg-slate-100">{{ __('Try a demo account') }}</a>@endguest
</section>
<div class="mb-6 flex flex-wrap gap-2">
    <a href="{{ route('home') }}" @class(['btn', 'bg-slate-900 text-white' => ! $level, 'btn-ghost' => $level])>{{ __('All levels') }}</a>
    @foreach (['beginner', 'intermediate', 'advanced'] as $l)
        <a href="{{ route('home', ['level' => $l]) }}" @class(['btn', 'bg-slate-900 text-white' => $level === $l, 'btn-ghost' => $level !== $l])>{{ __(ucfirst($l)) }}</a>
    @endforeach
</div>
<div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($courses as $course)
        <a href="{{ route('courses.show', $course) }}" class="card group flex flex-col overflow-hidden transition hover:shadow-md">
            <div class="h-28 p-4 text-white" style="background: linear-gradient(135deg, {{ $course->color }}, #0f172a)">
                <span class="badge bg-white/20 text-white">{{ __(ucfirst($course->level)) }}</span>
            </div>
            <div class="flex flex-1 flex-col p-4">
                <h2 class="font-semibold group-hover:underline">{{ $course->translate('title') }}</h2>
                <p class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $course->translate('summary') }}</p>
                <div class="mt-auto flex flex-wrap gap-x-3 pt-4 text-xs text-slate-500">
                    <span>{{ $course->instructor->name }}</span>
                    <span>{{ trans_choice(':count lesson|:count lessons', $course->lessons_count) }}</span>
                    <span>{{ __(':min min', ['min' => (int) $course->minutes]) }}</span>
                    <span>{{ trans_choice(':count learner|:count learners', $course->enrollments_count) }}</span>
                </div>
            </div>
        </a>
    @empty
        <p class="text-slate-500">{{ __('No courses yet.') }}</p>
    @endforelse
</div>
@endsection
