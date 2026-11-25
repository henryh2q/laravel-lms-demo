@extends('layouts.app')
@section('title', __('Performance'))
@section('content')
<h1 class="text-2xl font-bold">{{ __('Load testing') }}</h1>
<p class="mt-2 max-w-3xl text-stone-600">{{ __('How this demo is load tested with Locust: simulated students browse the catalog, open courses, read lessons and submit quizzes. The script and the raw numbers are in the repository.') }}</p>

@if ($results)
    <div class="mt-6 rounded-lg bg-brand-50 p-4 text-sm text-stone-700">
        <strong>{{ __('Environment') }}:</strong> {{ $results['environment'] }} · <strong>{{ __('Date') }}:</strong> {{ $results['date'] }}
    </div>
    <div class="mt-6 grid gap-4 md:grid-cols-{{ min(3, count($results['runs'])) }}">
        @foreach ($results['runs'] as $run)
            <div class="card p-5">
                <div class="text-sm text-stone-500">{{ __(':users concurrent users', ['users' => number_format($run['users'])]) }}</div>
                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-stone-500">{{ __('Requests/s') }}</dt><dd class="text-xl font-bold">{{ $run['rps'] }}</dd></div>
                    <div><dt class="text-stone-500">{{ __('Failures') }}</dt><dd class="text-xl font-bold">{{ $run['failure_rate'] }}%</dd></div>
                    <div><dt class="text-stone-500">p50</dt><dd class="text-xl font-bold">{{ $run['p50_ms'] }} ms</dd></div>
                    <div><dt class="text-stone-500">p95</dt><dd class="text-xl font-bold">{{ $run['p95_ms'] }} ms</dd></div>
                </dl>
                <div class="mt-3 text-xs text-stone-500">{{ __(':n requests over :s s', ['n' => number_format($run['requests']), 's' => $run['duration_s']]) }}</div>
            </div>
        @endforeach
    </div>
    @if (! empty($results['notes']))
        <ul class="mt-6 list-disc space-y-1 pl-5 text-sm text-stone-700">@foreach ($results['notes'] as $note)<li>{{ $note }}</li>@endforeach</ul>
    @endif
@else
    <div class="card mt-6 p-6 text-stone-600">{{ __('Results will be published here after the next test run.') }}</div>
@endif

<h2 class="mb-3 mt-10 text-lg font-semibold">{{ __('What makes it fast') }}</h2>
<ul class="grid gap-3 text-sm md:grid-cols-2">
    @foreach ([
        __('Catalog pages are cached with a version key that is bumped whenever a course, lesson or question changes.'),
        __('Eager loading and aggregate queries (withCount, withSum) instead of N+1 queries.'),
        __('Composite indexes on the hot paths: enrollments (user, course), lessons (course, position).'),
        __('Progress is stored on the enrollment, so dashboards do not recount lessons on every request.'),
    ] as $point)
        <li class="card p-4">{{ $point }}</li>
    @endforeach
</ul>

@if ($locustfile)
    <h2 class="mb-3 mt-10 text-lg font-semibold">locustfile.py</h2>
    <pre class="card overflow-x-auto p-4 text-xs">{{ $locustfile }}</pre>
@endif
@endsection
