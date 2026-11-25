@extends('layouts.app')
@section('title', __('Sign up'))
@section('content')
<form method="POST" action="{{ route('register') }}" class="card mx-auto max-w-md space-y-4 p-6">
    @csrf
    <h1 class="text-xl font-bold">{{ __('Create a student account') }}</h1>
    <div><label class="label" for="name">{{ __('Name') }}</label><input id="name" name="name" value="{{ old('name') }}" class="input" required></div>
    <div><label class="label" for="email">{{ __('Email') }}</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="input" required></div>
    <div><label class="label" for="password">{{ __('Password') }}</label><input id="password" name="password" type="password" class="input" required minlength="8"></div>
    <div><label class="label" for="password_confirmation">{{ __('Confirm password') }}</label><input id="password_confirmation" name="password_confirmation" type="password" class="input" required></div>
    <button class="btn-primary w-full">{{ __('Sign up') }}</button>
</form>
@endsection
