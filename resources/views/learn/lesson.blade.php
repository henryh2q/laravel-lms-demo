@extends('layouts.app')
@section('title', $lesson->translate('title'))
@section('content')
<div class="grid gap-6 lg:grid-cols-4">
    <aside class="card h-fit p-4 lg:sticky lg:top-4">
        <a href="{{ route('courses.show', $course) }}" class="text-sm font-semibold hover:underline">{{ $course->translate('title') }}</a>
        <ol class="mt-3 space-y-1 text-sm">
            @foreach ($lessons as $l)
                <li><a href="{{ route('lessons.show', [$course, $l->position]) }}" @class(['flex gap-2 rounded px-2 py-1', 'bg-brand-50 font-semibold' => $l->id === $lesson->id, 'hover:bg-stone-50' => $l->id !== $lesson->id])>
                    <span class="w-4 text-emerald-600">{{ in_array($l->id, $done) ? '✓' : '' }}</span>{{ $l->translate('title') }}</a></li>
            @endforeach
            <li><a href="{{ route('quiz.show', $course) }}" class="flex gap-2 rounded px-2 py-1 hover:bg-stone-50"><span class="w-4">?</span>{{ __('Final quiz') }}</a></li>
        </ol>
    </aside>
    <article class="card p-6 lg:col-span-3 md:p-8">
        <div class="text-xs text-stone-500">{{ __('Lesson :n of :total', ['n' => $lesson->position, 'total' => $lessons->count()]) }} · {{ __(':min min', ['min' => $lesson->duration_minutes]) }}</div>
        <h1 class="mt-1 text-2xl font-bold">{{ $lesson->translate('title') }}</h1>
        <div class="prose-lesson mt-6 space-y-4 leading-relaxed text-stone-700">{!! Str::markdown($lesson->translate('body'), ['html_input' => 'escape', 'allow_unsafe_links' => false]) !!}</div>
        <form method="POST" action="{{ route('lessons.complete', [$course, $lesson->position]) }}" data-once class="mt-8 flex justify-end border-t border-stone-100 pt-6">
            @csrf
            <button type="submit" class="btn-primary">{{ in_array($lesson->id, $done) ? __('Next') : __('Mark complete & continue') }} →</button>
        </form>
    </article>
</div>
@endsection
