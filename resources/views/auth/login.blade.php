@extends('layouts.app')
@section('title', __('Log in'))
@section('content')
<div class="mx-auto grid max-w-4xl gap-6 md:grid-cols-2">
    <div class="card p-6">
        <h1 class="text-xl font-bold">{{ __('Try a demo account') }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ __('One click, no password needed. Each role sees a different part of the app.') }}</p>
        <div class="mt-5 space-y-3">
            @foreach (['student' => __('Browse, enroll, finish lessons, take quizzes.'), 'instructor' => __('Manage own courses, lessons, quizzes; see students.'), 'admin' => __('Everything, plus user roles and all courses.')] as $role => $text)
                <form method="POST" action="{{ route('demo-login', $role) }}" class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3">
                    @csrf
                    <div><div class="font-semibold">{{ __(ucfirst($role)) }}</div><div class="text-xs text-slate-500">{{ $text }}</div></div>
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
        <p class="text-center text-sm text-slate-500"><a href="{{ route('register') }}" class="underline">{{ __('Create a student account') }}</a></p>
    </form>
</div>
@endsection
