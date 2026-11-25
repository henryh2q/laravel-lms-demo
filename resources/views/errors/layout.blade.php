{{-- Standalone error layout: no database, session or cart access, so it renders even when those are down. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $code }} · Manabi LMS (demo)</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 p-6 text-slate-900 antialiased">
    <main class="max-w-md text-center">
        <a href="/" class="text-lg font-bold tracking-tight">Manabi<span class="text-brand-600">LMS</span></a>
        <p class="mt-10 text-6xl font-bold text-brand-600">{{ $code }}</p>
        <h1 class="mt-4 text-2xl font-bold">{{ $title }}</h1>
        <p class="mt-2 text-slate-600">{{ $message }}</p>
        <a href="/" class="btn-primary mt-8">{{ __('Back to courses') }}</a>
    </main>
</body>
</html>
