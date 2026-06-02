<?php

use App\Exceptions\EscrowException;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\OrderException;
use App\Exceptions\PaymentException;
use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\EnsureIsSeller;
use App\Http\Middleware\EnsureSellerVerified;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'seller' => EnsureIsSeller::class,
            'admin' => EnsureIsAdmin::class,
            'seller.verified' => EnsureSellerVerified::class,
        ]);

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Always render JSON for API routes
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // ─── Business Logic Exceptions (Custom) ─────────────────────────

        // Order exceptions → 422
        $exceptions->renderable(function (OrderException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }
        });

        // Escrow exceptions → 422
        $exceptions->renderable(function (EscrowException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }
        });

        // Payment exceptions → 422
        $exceptions->renderable(function (PaymentException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }
        });

        // Insufficient balance → 422
        $exceptions->renderable(function (InsufficientBalanceException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => [
                        'balance' => [$e->getMessage()],
                    ],
                    'data' => [
                        'required_amount' => $e->requiredAmount,
                        'current_balance' => $e->currentBalance,
                    ],
                ], 422);
            }
        });

        // RuntimeException from services (business rule violations) → 422
        $exceptions->renderable(function (RuntimeException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Only handle RuntimeExceptions thrown by our app code, not framework internals
                $file = $e->getFile();
                if (str_contains($file, 'app/Services/') || str_contains($file, 'app/Exceptions/')) {
                    return response()->json([
                        'message' => $e->getMessage(),
                    ], 422);
                }
            }
        });

        // ─── Laravel Framework Exceptions ────────────────────────────────

        // Model not found → 404
        $exceptions->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $model = class_basename($e->getModel());
                $modelLabels = [
                    'User' => 'Pengguna',
                    'Product' => 'Produk',
                    'Order' => 'Pesanan',
                    'Category' => 'Kategori',
                    'Brand' => 'Brand',
                    'UserAddress' => 'Alamat',
                    'UserBankAccount' => 'Rekening bank',
                    'Conversation' => 'Percakapan',
                    'Message' => 'Pesan',
                    'Review' => 'Ulasan',
                    'Withdrawal' => 'Penarikan',
                    'Dispute' => 'Sengketa',
                    'DisputeEvidence' => 'Bukti sengketa',
                    'Shipment' => 'Pengiriman',
                    'Payment' => 'Pembayaran',
                    'EscrowTransaction' => 'Transaksi escrow',
                    'Notification' => 'Notifikasi',
                    'Bookmark' => 'Bookmark',
                    'ProductImage' => 'Gambar produk',
                    'Province' => 'Provinsi',
                    'City' => 'Kota',
                ];
                $label = $modelLabels[$model] ?? $model;

                return response()->json([
                    'message' => "{$label} tidak ditemukan.",
                ], 404);
            }
        });

        // Route not found → 404
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Endpoint tidak ditemukan.',
                ], 404);
            }
        });

        // Unauthenticated → 401
        $exceptions->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Anda belum login. Silakan login terlebih dahulu.',
                ], 401);
            }
        });

        // Unauthorized → 403
        $exceptions->renderable(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage() ?: 'Anda tidak memiliki izin untuk melakukan aksi ini.',
                ], 403);
            }
        });

        // ─── Database Exceptions ─────────────────────────────────────────

        $exceptions->renderable(function (QueryException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $errorCode = $e->errorInfo[0] ?? null;

                // Unique constraint violation (PostgreSQL: 23505)
                if ($errorCode === '23505') {
                    $message = $e->getMessage();
                    $field = 'data';

                    if (preg_match('/Key \((\w+)\)=/', $message, $matches)) {
                        $field = $matches[1];
                    }

                    $fieldLabels = [
                        'email' => 'Email sudah terdaftar.',
                        'phone' => 'Nomor telepon sudah terdaftar.',
                        'slug' => 'Slug sudah digunakan.',
                        'order_number' => 'Nomor pesanan sudah ada.',
                        'midtrans_order_id' => 'ID transaksi Midtrans sudah ada.',
                        'account_number' => 'Nomor rekening sudah terdaftar.',
                        'token' => 'Token sudah digunakan.',
                        'tracking_number' => 'Nomor resi sudah digunakan.',
                    ];

                    $errorMessage = $fieldLabels[$field] ?? "Data dengan {$field} tersebut sudah ada.";

                    return response()->json([
                        'message' => $errorMessage,
                        'errors' => [
                            $field => [$errorMessage],
                        ],
                    ], 422);
                }

                // Foreign key violation (PostgreSQL: 23503)
                if ($errorCode === '23503') {
                    return response()->json([
                        'message' => 'Data referensi tidak valid atau tidak ditemukan.',
                    ], 422);
                }

                // Not null violation (PostgreSQL: 23502)
                if ($errorCode === '23502') {
                    $field = 'data';
                    if (preg_match('/column "(\w+)"/', $e->getMessage(), $matches)) {
                        $field = $matches[1];
                    }

                    return response()->json([
                        'message' => "Kolom {$field} tidak boleh kosong.",
                        'errors' => [
                            $field => ["Kolom {$field} wajib diisi."],
                        ],
                    ], 422);
                }

                // Check constraint violation (PostgreSQL: 23514)
                if ($errorCode === '23514') {
                    return response()->json([
                        'message' => 'Data tidak memenuhi persyaratan yang ditentukan.',
                    ], 422);
                }

                // String data too long (PostgreSQL: 22001)
                if ($errorCode === '22001') {
                    return response()->json([
                        'message' => 'Data terlalu panjang melebihi batas yang ditentukan.',
                    ], 422);
                }

                // Invalid text representation / type mismatch (PostgreSQL: 22P02)
                if ($errorCode === '22P02') {
                    return response()->json([
                        'message' => 'Format data tidak valid.',
                    ], 422);
                }

                // In production, hide DB details
                if (app()->environment('production')) {
                    return response()->json([
                        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi.',
                    ], 500);
                }

                // In local/dev, show the actual error for debugging
                return response()->json([
                    'message' => 'Database error.',
                    'error' => $e->getMessage(),
                    'code' => $errorCode,
                ], 500);
            }
        });

        // ─── Generic HTTP exceptions ─────────────────────────────────────

        $exceptions->renderable(function (HttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $statusMessages = [
                    400 => 'Permintaan tidak valid.',
                    403 => 'Anda tidak memiliki izin untuk melakukan aksi ini.',
                    404 => 'Data tidak ditemukan.',
                    405 => 'Metode HTTP tidak diizinkan untuk endpoint ini.',
                    408 => 'Permintaan timeout.',
                    409 => 'Terjadi konflik data.',
                    413 => 'Ukuran data terlalu besar.',
                    429 => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                    500 => 'Terjadi kesalahan pada server.',
                    502 => 'Server sedang tidak dapat dijangkau.',
                    503 => 'Layanan sedang dalam pemeliharaan.',
                ];

                return response()->json([
                    'message' => $e->getMessage() ?: ($statusMessages[$e->getStatusCode()] ?? 'Terjadi kesalahan.'),
                ], $e->getStatusCode());
            }
        });

        // ─── Catch-all ───────────────────────────────────────────────────

        $exceptions->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                // Let ValidationException be handled by Laravel's default (already clean JSON)
                if ($e instanceof ValidationException) {
                    return;
                }

                if (app()->environment('production')) {
                    return response()->json([
                        'message' => 'Terjadi kesalahan pada server. Silakan coba lagi.',
                    ], 500);
                }

                return response()->json([
                    'message' => $e->getMessage(),
                    'exception' => class_basename($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ], 500);
            }
        });

    })->create();
