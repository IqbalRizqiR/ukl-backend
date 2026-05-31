<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_admin) {
            return new JsonResponse([
                'message' => 'Akses ditolak. Hanya admin yang diizinkan.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
