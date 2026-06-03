<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_seller_verified === 'true') {
            return new JsonResponse([
                'message' => 'Akun penjual Anda belum diverifikasi.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
