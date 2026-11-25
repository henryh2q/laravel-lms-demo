<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title', __('Courses')) · Manabi LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <div class="bg-slate-900 px-4 py-2 text-center text-xs text-slate-100">
        {{ __('Portfolio demo built by Hai H. Not a real school: sample courses, shared demo accounts, data is reset regularly.') }}
    </div>
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-4 py-4">
            <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight">Manabi<span class="text-brand-600">LMS</span></a>
            <nav class="flex gap-4 text-sm text-slate-600">
                <a href="{{ route('home') }}" class="hover:text-slate-900">{{ __('Courses') }}</a>
                @auth<a href="{{ route('learning.dashboard') }}" class="hover:text-slate-900">{{ __('My learning') }}</a>@endauth
                <a href="{{ route('performance') }}" class="hover:text-slate-900">{{ __('Performance') }}</a>
                @if (auth()->user()?->canTeach())<a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">{{ __('Admin') }}</a>@endif
            </nav>
            <div class="ml-auto flex items-center gap-3">
                <div class="flex overflow-hidden rounded-lg border border-slate-200 text-xs">
                    @foreach (config('app.supported_locales') as $code => $label)
                        <a href="{{ route('locale', $code) }}" @class(['px-2 py-1', 'bg-slate-900 text-white' => app()->getLocale() === $code, 'hover:bg-slate-100' => app()->getLocale() !== $code])>{{ strtoupper($code) }}</a>
                    @endforeach
                </div>
                @auth
                    <span class="hidden text-sm text-slate-600 sm:inline">{{ auth()->user()->name }} <span class="badge bg-slate-100 text-slate-600">{{ __(auth()->user()->role) }}</span></span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn-ghost">{{ __('Log out') }}</button></form>
                @else
                    <a href="{{ route('login') }}" class="btn-primary">{{ __('Log in') }}</a>
                @endauth
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-4 py-8">
        <x-flash />
        @yield('content')
    </main>
    <footer class="border-t border-slate-200 py-8 text-center text-xs text-slate-500">
        {{ __('Laravel + Blade + MySQL · Roles: admin / instructor / student · Load tested with Locust · EN / 日本語 / Tiếng Việt') }}
    </footer>
</body>
</html>
