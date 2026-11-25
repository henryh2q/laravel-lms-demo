@extends('layouts.app')
@section('title', __('Courses'))
@section('content')
<section class="mb-12 grid items-center gap-8 md:grid-cols-2">
    <div>
        <span class="badge bg-brand-100 px-3 py-1 text-brand-700">{{ __('Free demo courses') }}</span>
        <h1 class="mt-4 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">{{ __('Learn at your own pace') }}</h1>
        <p class="mt-4 max-w-xl text-lg text-stone-600">{{ __('A working LMS demo: enroll in a course, finish lessons, pass the quiz and track progress. Instructors and admins manage content from their own panel.') }}</p>
        <div class="mt-6 flex flex-wrap gap-3">
            @guest
                <a href="{{ route('login') }}" class="btn-primary px-6 py-3 text-base">{{ __('Try a demo account') }}</a>
            @else
                <a href="{{ route('learning.dashboard') }}" class="btn-primary px-6 py-3 text-base">{{ __('My learning') }}</a>
            @endguest
            <a href="#courses" class="btn-ghost px-6 py-3 text-base">{{ __('Browse courses') }}</a>
        </div>
    </div>
    <figure class="relative">
        <div class="absolute -right-3 -top-3 size-24 rounded-3xl bg-brand-100"></div>
        <div class="absolute -bottom-3 -left-3 size-20 rounded-full bg-amber-100"></div>
        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=900&h=640&fit=crop&auto=format&q=75" alt="{{ __('Students learning together around a laptop') }}" width="900" height="640" class="relative aspect-[45/32] w-full rounded-3xl object-cover shadow-lg">
        <div class="absolute bottom-4 left-4 flex items-center gap-2 rounded-2xl bg-white/95 px-3 py-2 text-sm font-semibold shadow">
            <span class="flex size-7 items-center justify-center rounded-full bg-emerald-500 text-white">✓</span>{{ __('Lesson completed!') }}
        </div>
        <figcaption class="relative mt-2 text-right text-xs text-stone-400">{{ __('Photo') }}: <a href="https://unsplash.com/@brookecagle?utm_source=manabi_lms_demo&utm_medium=referral" class="underline" rel="noopener">Brooke Cagle</a> / Unsplash</figcaption>
    </figure>
</section>

<section class="mb-12 grid gap-4 sm:grid-cols-3">
    @foreach ([['📚', __('Enroll in a course'), __('Pick a course and start right away. No payment, just a demo account.')], ['✍️', __('Learn step by step'), __('Short lessons, progress saved after every step.')], ['🎓', __('Pass the quiz'), __('A short final quiz, graded on the server.')]] as [$icon, $title, $text])
        <div class="card flex gap-4 p-5">
            <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-2xl" aria-hidden="true">{{ $icon }}</span>
            <div><h2 class="font-semibold">{{ $title }}</h2><p class="mt-1 text-sm text-stone-600">{{ $text }}</p></div>
        </div>
    @endforeach
</section>

<div id="courses" class="mb-6 flex flex-wrap items-end justify-between gap-4 scroll-mt-24">
    <h2 class="text-2xl font-bold">{{ __('Courses') }}</h2>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('home') }}#courses" @class(['btn', 'bg-stone-800 text-white' => ! $level, 'btn-ghost' => $level])>{{ __('All levels') }}</a>
        @foreach (['beginner', 'intermediate', 'advanced'] as $l)
            <a href="{{ route('home', ['level' => $l]) }}#courses" @class(['btn', 'bg-stone-800 text-white' => $level === $l, 'btn-ghost' => $level !== $l])>{{ __(ucfirst($l)) }}</a>
        @endforeach
    </div>
</div>
<div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($courses as $course)
        <a href="{{ route('courses.show', $course) }}" class="card group flex flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-lg">
            <div class="relative aspect-video overflow-hidden" style="background: {{ $course->color }}">
                @if ($src = $course->imageSrc(640, 360))
                    <img src="{{ $src }}" alt="" loading="lazy" width="640" height="360" class="size-full object-cover transition duration-500 group-hover:scale-105">
                @endif
                <span class="badge absolute left-3 top-3 bg-white/95 px-3 py-1 text-stone-700 shadow-sm">{{ __(ucfirst($course->level)) }}</span>
            </div>
            <div class="flex flex-1 flex-col p-5">
                <h3 class="text-lg font-semibold leading-snug group-hover:text-brand-700">{{ $course->translate('title') }}</h3>
                <p class="mt-2 line-clamp-2 text-sm text-stone-600">{{ $course->translate('summary') }}</p>
                <div class="mt-auto flex items-center gap-3 pt-5">
                    <span class="flex size-8 items-center justify-center rounded-full text-xs font-bold text-white" style="background: {{ $course->color }}">{{ mb_substr($course->instructor->name, 0, 1) }}</span>
                    <span class="text-sm font-medium">{{ $course->instructor->name }}</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-2 text-xs text-stone-500">
                    <span class="rounded-full bg-stone-100 px-2 py-0.5">{{ trans_choice(':count lesson|:count lessons', $course->lessons_count) }}</span>
                    <span class="rounded-full bg-stone-100 px-2 py-0.5">{{ __(':min min', ['min' => (int) $course->minutes]) }}</span>
                    <span class="rounded-full bg-stone-100 px-2 py-0.5">{{ trans_choice(':count learner|:count learners', $course->enrollments_count) }}</span>
                </div>
            </div>
        </a>
    @empty
        <p class="text-stone-500">{{ __('No courses yet.') }}</p>
    @endforelse
</div>
@endsection
