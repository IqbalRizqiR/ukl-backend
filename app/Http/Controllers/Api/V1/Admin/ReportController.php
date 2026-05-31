<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    public function transactions(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $report = $this->reportService->transactions($request->only('start_date', 'end_date'));

        return response()->json([
            'message' => 'Laporan transaksi berhasil diambil.',
            'data' => $report,
        ]);
    }

    public function overview(): JsonResponse
    {
        $report = $this->reportService->overview();

        return response()->json([
            'message' => 'Laporan overview berhasil diambil.',
            'data' => $report,
        ]);
    }
}
