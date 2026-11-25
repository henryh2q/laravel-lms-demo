@extends('layouts.app')
@section('title', __('Final quiz'))
@section('content')
<a href="{{ route('courses.show', $course) }}" class="text-sm text-slate-500 hover:underline">← {{ $course->translate('title') }}</a>
<h1 class="mb-2 mt-2 text-2xl font-bold">{{ __('Final quiz') }}</h1>
<p class="mb-6 text-sm text-slate-600">{{ __('Score at least :pass% to complete the course. Answers are graded on the server.', ['pass' => $course->pass_score]) }}</p>
@if ($last)
    <div @class(['mb-6 rounded-lg p-4 text-sm', 'bg-emerald-50 text-emerald-800' => $last->passed, 'bg-amber-50 text-amber-800' => ! $last->passed])>
        {{ __('Last attempt: :score / :total (:percent%)', ['score' => $last->score, 'total' => $last->total, 'percent' => $last->percent()]) }}
    </div>
@endif
<form method="POST" action="{{ route('quiz.submit', $course) }}" data-once class="space-y-4">
    @csrf
    @foreach ($questions as $i => $question)
        <fieldset class="card p-5">
            <legend class="sr-only">{{ __('Question :n', ['n' => $i + 1]) }}</legend>
            <div class="mb-3 font-medium">{{ $i + 1 }}. {{ $question->translate('prompt') }}</div>
            <div class="space-y-2">
                @foreach ($question->translatedOptions() as $k => $option)
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 p-3 hover:bg-slate-50 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $k }}" required @checked(old("answers.{$question->id}") == (string) $k)> {{ $option }}
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endforeach
    <button type="submit" class="btn-primary">{{ __('Submit answers') }}</button>
</form>
@endsection
