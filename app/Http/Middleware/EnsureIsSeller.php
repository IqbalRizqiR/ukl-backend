<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsSeller
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_seller === 'true') {
            return new JsonResponse([
                'message' => 'Anda harus mengaktifkan mode penjual terlebih dahulu.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
