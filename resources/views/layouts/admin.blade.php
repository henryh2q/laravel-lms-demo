<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
    <title>@yield('title', 'Dashboard') · LMS admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
<div class="flex min-h-screen flex-col md:flex-row">
    <aside class="bg-slate-900 text-slate-300 md:w-56 md:shrink-0">
        <div class="px-5 py-5 text-lg font-bold text-white">Manabi<span class="text-sky-400">LMS</span> <span class="text-xs font-normal text-slate-400">{{ auth()->user()->role }}</span></div>
        <nav class="flex gap-1 overflow-x-auto px-3 pb-3 text-sm md:flex-col">
            @php
                $items = [['admin.dashboard', 'Dashboard', 'admin.dashboard'], ['admin.courses.index', 'Courses', 'admin.courses.*']];
                if (auth()->user()->isAdmin()) { $items[] = ['admin.users.index', 'Users', 'admin.users.*']; }
            @endphp
            @foreach ($items as [$route, $label, $pattern])
                <a href="{{ route($route) }}" @class(['whitespace-nowrap rounded-lg px-3 py-2', 'bg-slate-800 text-white' => request()->routeIs($pattern), 'hover:bg-slate-800' => ! request()->routeIs($pattern)])>{{ $label }}</a>
            @endforeach
            <a href="{{ route('home') }}" class="whitespace-nowrap rounded-lg px-3 py-2 hover:bg-slate-800">View site ↗</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full whitespace-nowrap rounded-lg px-3 py-2 text-left hover:bg-slate-800">Log out</button></form>
        </nav>
    </aside>
    <main class="min-w-0 flex-1 p-4 md:p-8">
        <div class="mb-4 rounded-lg bg-amber-50 px-4 py-2 text-xs text-amber-800">Portfolio demo with shared accounts. Sample data is reset regularly.</div>
        <x-flash />
        @yield('content')
    </main>
</div>
</body>
</html>
