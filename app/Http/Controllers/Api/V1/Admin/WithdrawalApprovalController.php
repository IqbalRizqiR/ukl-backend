<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Withdrawal\WithdrawalResource;
use App\Services\Admin\WithdrawalApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WithdrawalApprovalController extends Controller
{
    public function __construct(
        private readonly WithdrawalApprovalService $withdrawalApprovalService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $withdrawals = $this->withdrawalApprovalService->list($request->all());

        return WithdrawalResource::collection($withdrawals);
    }

    public function approve(string $withdrawalId): JsonResponse
    {
        $withdrawal = $this->withdrawalApprovalService->approve($withdrawalId);

        return response()->json([
            'message' => 'Penarikan berhasil disetujui.',
            'data' => new WithdrawalResource($withdrawal),
        ]);
    }

    public function reject(Request $request, string $withdrawalId): JsonResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $withdrawal = $this->withdrawalApprovalService->reject(
            $withdrawalId,
            $request->input('rejection_reason'),
        );

        return response()->json([
            'message' => 'Penarikan berhasil ditolak.',
            'data' => new WithdrawalResource($withdrawal),
        ]);
    }
}
