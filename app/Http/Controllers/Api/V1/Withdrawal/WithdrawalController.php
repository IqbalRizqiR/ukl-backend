<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Withdrawal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Withdrawal\StoreWithdrawalRequest;
use App\Http\Resources\Withdrawal\WithdrawalResource;
use App\Services\Withdrawal\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class WithdrawalController extends Controller
{
    public function __construct(
        private readonly WithdrawalService $withdrawalService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $withdrawals = $this->withdrawalService->getUserWithdrawals(
            $request->user()->id,
            $request->integer('per_page', 15),
        );

        return WithdrawalResource::collection($withdrawals);
    }

    public function store(StoreWithdrawalRequest $request): JsonResponse
    {
        $withdrawal = $this->withdrawalService->request(
            $request->user()->id,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Permintaan penarikan berhasil dibuat.',
            'data' => new WithdrawalResource($withdrawal),
        ], 201);
    }
}
