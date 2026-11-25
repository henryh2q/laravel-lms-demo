<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title', __('Courses')) · Manabi LMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream text-stone-800 antialiased">
    <div class="bg-stone-800 px-4 py-2 text-center text-xs text-stone-100">
        {{ __('Portfolio demo built by Hai H. Not a real school: sample courses, shared demo accounts, data is reset regularly.') }}
    </div>
    <header class="sticky top-0 z-10 border-b border-stone-200/70 bg-white/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-4 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-bold tracking-tight"><span class="flex size-8 items-center justify-center rounded-xl bg-brand-600 text-base text-white">学</span>Manabi<span class="-ml-1.5 text-brand-600">LMS</span></a>
            <nav class="flex gap-1 text-sm font-medium text-stone-600">
                <a href="{{ route('home') }}" class="rounded-full px-3 py-1 hover:bg-brand-50 hover:text-brand-700">{{ __('Courses') }}</a>
                @auth<a href="{{ route('learning.dashboard') }}" class="rounded-full px-3 py-1 hover:bg-brand-50 hover:text-brand-700">{{ __('My learning') }}</a>@endauth
                <a href="{{ route('performance') }}" class="rounded-full px-3 py-1 hover:bg-brand-50 hover:text-brand-700">{{ __('Performance') }}</a>
                @if (auth()->user()?->canTeach())<a href="{{ route('admin.dashboard') }}" class="rounded-full px-3 py-1 hover:bg-brand-50 hover:text-brand-700">{{ __('Admin') }}</a>@endif
            </nav>
            <div class="ml-auto flex items-center gap-3">
                <div class="flex overflow-hidden rounded-full border border-stone-200 text-xs">
                    @foreach (config('app.supported_locales') as $code => $label)
                        <a href="{{ route('locale', $code) }}" @class(['px-2.5 py-1', 'bg-brand-600 text-white' => app()->getLocale() === $code, 'hover:bg-brand-50' => app()->getLocale() !== $code])>{{ strtoupper($code) }}</a>
                    @endforeach
                </div>
                @auth
                    <span class="hidden text-sm text-stone-600 sm:inline">{{ auth()->user()->name }} <span class="badge bg-stone-100 text-stone-600">{{ __(auth()->user()->role) }}</span></span>
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
    <footer class="mt-8 border-t border-stone-200 py-8 text-center text-xs text-stone-500">
        {{ __('Laravel + Blade + MySQL · Roles: admin / instructor / student · Load tested with Locust · EN / 日本語 / Tiếng Việt') }}
    </footer>
</body>
</html>
