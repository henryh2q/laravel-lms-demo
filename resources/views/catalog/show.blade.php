@extends('layouts.app')
@section('title', $course->translate('title'))
@section('content')
@if ($src = $course->imageSrc(1200, 420))
    <figure class="mb-8">
        <div class="relative overflow-hidden rounded-3xl" style="background: {{ $course->color }}">
            <img src="{{ $src }}" alt="" width="1200" height="420" class="aspect-[20/7] w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/50 to-transparent"></div>
        </div>
        @if ($credit = $course->imageCredit())
            <figcaption class="mt-2 text-right text-xs text-stone-400">{{ __('Photo') }}: <a href="{{ $credit['url'] }}" class="underline" rel="noopener">{{ $credit['name'] }}</a> / Unsplash</figcaption>
        @endif
    </figure>
@endif
<div class="grid gap-8 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <span class="badge bg-brand-100 px-3 py-1 text-brand-700">{{ __(ucfirst($course->level)) }}</span>
        @unless ($course->is_published)<span class="badge bg-amber-100 text-amber-800">{{ __('Draft') }}</span>@endunless
        <h1 class="mt-2 text-3xl font-bold">{{ $course->translate('title') }}</h1>
        <p class="mt-3 text-stone-700">{{ $course->translate('summary') }}</p>
        <p class="mt-3 text-sm text-stone-500">{{ __('Instructor') }}: {{ $course->instructor->name }} · {{ trans_choice(':count learner|:count learners', $course->enrollments_count) }}</p>

        <h2 class="mb-3 mt-8 text-lg font-semibold">{{ __('Curriculum') }}</h2>
        <ol class="card divide-y divide-stone-100">
            @foreach ($course->lessons as $lesson)
                <li class="flex items-center gap-3 p-4">
                    <span @class(['flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold', 'bg-emerald-600 text-white' => in_array($lesson->id, $done), 'bg-brand-50 text-brand-700' => ! in_array($lesson->id, $done)])>{{ in_array($lesson->id, $done) ? '✓' : $lesson->position }}</span>
                    @if ($enrollment)
                        <a href="{{ route('lessons.show', [$course, $lesson->position]) }}" class="flex-1 hover:underline">{{ $lesson->translate('title') }}</a>
                    @else
                        <span class="flex-1">{{ $lesson->translate('title') }}</span>
                    @endif
                    <span class="text-xs text-stone-500">{{ __(':min min', ['min' => $lesson->duration_minutes]) }}</span>
                </li>
            @endforeach
            @if ($course->questions_count)
                <li class="flex items-center gap-3 p-4"><span class="flex size-7 items-center justify-center rounded-full bg-stone-100 text-xs">?</span><span class="flex-1">{{ __('Final quiz') }} ({{ trans_choice(':count question|:count questions', $course->questions_count) }}, {{ __('pass :pass%', ['pass' => $course->pass_score]) }})</span></li>
            @endif
        </ol>
    </div>
    <aside class="card h-fit space-y-4 p-6 lg:sticky lg:top-24">
        @if ($enrollment)
            <div class="text-sm text-stone-600">{{ __('Your progress') }}</div>
            <div class="text-3xl font-bold">{{ $enrollment->progress }}%</div>
            <x-progress :value="$enrollment->progress" :color="$course->color" />
            @if ($enrollment->completed_at)
                <div class="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">{{ __('Completed on :date', ['date' => $enrollment->completed_at->toDateString()]) }}</div>
            @endif
            @php $next = $course->lessons->first(fn ($l) => ! in_array($l->id, $done)); @endphp
            <a href="{{ $next ? route('lessons.show', [$course, $next->position]) : route('quiz.show', $course) }}" class="btn-primary w-full">{{ $next ? __('Continue learning') : __('Go to quiz') }}</a>
        @else
            <div class="text-sm text-stone-600">{{ __('Free demo course') }}</div>
            @auth
                <form method="POST" action="{{ route('courses.enroll', $course) }}" data-once>@csrf<button type="submit" class="btn-primary w-full">{{ __('Enroll now') }}</button></form>
            @else
                <a href="{{ route('login') }}" class="btn-primary w-full">{{ __('Log in to enroll') }}</a>
            @endauth
        @endif
    </aside>
</div>
@endsection
