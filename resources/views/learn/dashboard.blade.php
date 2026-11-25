@extends('layouts.app')
@section('title', __('My learning'))
@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ __('My learning') }}</h1>
<div class="grid gap-4 md:grid-cols-2">
    @forelse ($enrollments as $enrollment)
        <a href="{{ route('courses.show', $enrollment->course) }}" class="card flex gap-4 p-4 transition hover:shadow-md">
            @if ($src = $enrollment->course->imageSrc(240, 240))
                <img src="{{ $src }}" alt="" width="96" height="96" class="size-24 shrink-0 rounded-xl object-cover">
            @endif
            <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
                <h2 class="font-semibold">{{ $enrollment->course->translate('title') }}</h2>
                @if ($enrollment->completed_at)<span class="badge bg-emerald-100 text-emerald-800">{{ __('Completed') }}</span>@endif
            </div>
            <div class="mt-3 flex items-center gap-3"><x-progress :value="$enrollment->progress" :color="$enrollment->course->color" /><span class="text-sm font-semibold">{{ $enrollment->progress }}%</span></div>
            </div>
        </a>
    @empty
        <div class="card p-8 text-stone-500">{{ __('You have not enrolled in any course yet.') }} <a href="{{ route('home') }}" class="text-brand-600 underline">{{ __('Browse courses') }}</a></div>
    @endforelse
</div>
@if ($attempts->isNotEmpty())
    <h2 class="mb-3 mt-10 text-lg font-semibold">{{ __('Recent quiz results') }}</h2>
    <div class="card divide-y divide-stone-100">
        @foreach ($attempts as $attempt)
            <div class="flex items-center justify-between p-4 text-sm">
                <span>{{ $attempt->course->translate('title') }}</span>
                <span><strong>{{ $attempt->percent() }}%</strong> <span @class(['badge ml-2', 'bg-emerald-100 text-emerald-800' => $attempt->passed, 'bg-red-100 text-red-700' => ! $attempt->passed])>{{ $attempt->passed ? __('Passed') : __('Not passed') }}</span></span>
            </div>
        @endforeach
    </div>
@endif
@endsection
