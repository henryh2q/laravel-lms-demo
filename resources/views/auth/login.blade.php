@extends('layouts.app')
@section('title', __('Log in'))
@section('content')
<div class="mx-auto mb-8 max-w-4xl text-center">
    <span class="text-4xl" aria-hidden="true">👋</span>
    <h1 class="mt-2 text-3xl font-extrabold">{{ __('Welcome to Manabi') }}</h1>
    <p class="mt-2 text-stone-600">{{ __('Pick a role to look around. Nothing to install, nothing to pay.') }}</p>
</div>
<div class="mx-auto grid max-w-4xl gap-6 md:grid-cols-2">
    <div class="card p-6">
        <h2 class="text-xl font-bold">{{ __('Try a demo account') }}</h2>
        <p class="mt-1 text-sm text-stone-600">{{ __('One click, no password needed. Each role sees a different part of the app.') }}</p>
        <div class="mt-5 space-y-3">
            @foreach (['student' => __('Browse, enroll, finish lessons, take quizzes.'), 'instructor' => __('Manage own courses, lessons, quizzes; see students.'), 'admin' => __('Everything, plus user roles and all courses.')] as $role => $text)
                <form method="POST" action="{{ route('demo-login', $role) }}" class="flex items-center justify-between gap-3 rounded-2xl border border-stone-200 p-3 transition hover:border-brand-500 hover:bg-brand-50">
                    @csrf
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-brand-50 text-xl" aria-hidden="true">{{ ['student' => '🧑‍🎓', 'instructor' => '👩‍🏫', 'admin' => '🛠️'][$role] }}</span><div class="flex-1"><div class="font-semibold">{{ __(ucfirst($role)) }}</div><div class="text-xs text-stone-500">{{ $text }}</div></div>
                    <button class="btn-primary shrink-0">{{ __('Enter') }}</button>
                </form>
            @endforeach
        </div>
    </div>
    <form method="POST" action="{{ route('login') }}" class="card space-y-4 p-6">
        @csrf
        <h2 class="text-xl font-bold">{{ __('Log in') }}</h2>
        <div><label class="label" for="email">{{ __('Email') }}</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="input" required></div>
        <div><label class="label" for="password">{{ __('Password') }}</label><input id="password" name="password" type="password" class="input" required></div>
        <button class="btn-primary w-full">{{ __('Log in') }}</button>
        <p class="text-center text-sm text-stone-500"><a href="{{ route('register') }}" class="underline">{{ __('Create a student account') }}</a></p>
    </form>
</div>
@endsection
