<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanTeach
{
    public function handle(Request $request, Closure $next, string $role = 'instructor'): Response
    {
        $user = $request->user();
        $allowed = $role === 'admin' ? $user?->isAdmin() : $user?->canTeach();

        abort_unless($allowed, 403);

        return $next($request);
    }
}
