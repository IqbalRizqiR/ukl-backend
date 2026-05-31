<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMidtransSignature
{
    /**
     * Handle an incoming request.
     *
     * Verifies the Midtrans webhook signature by comparing the SHA-512 hash
     * of order_id + status_code + gross_amount + server_key against the
     * signature_key sent in the payload.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $orderId = $request->input('order_id', '');
        $statusCode = $request->input('status_code', '');
        $grossAmount = $request->input('gross_amount', '');
        $signatureKey = $request->input('signature_key', '');

        $serverKey = config('midtrans.server_key');

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (! hash_equals($expectedSignature, $signatureKey)) {
            return new JsonResponse([
                'message' => 'Signature tidak valid.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
