<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreBankAccountRequest;
use App\Http\Resources\User\BankAccountResource;
use App\Services\User\BankAccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class BankAccountController extends Controller
{
    public function __construct(
        private readonly BankAccountService $bankAccountService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $accounts = $this->bankAccountService->getByUser($request->user()->id);

        return BankAccountResource::collection($accounts);
    }

    public function store(StoreBankAccountRequest $request): JsonResponse
    {
        $account = $this->bankAccountService->create(
            $request->user()->id,
            $request->validated(),
        );

        return response()->json([
            'message' => 'Rekening bank berhasil ditambahkan.',
            'data' => new BankAccountResource($account),
        ], 201);
    }

    public function destroy(Request $request, string $bankAccountId): JsonResponse
    {
        $this->bankAccountService->delete($request->user()->id, $bankAccountId);

        return response()->json([
            'message' => 'Rekening bank berhasil dihapus.',
        ]);
    }

    public function setDefault(Request $request, string $bankAccountId): JsonResponse
    {
        $account = $this->bankAccountService->setDefault($request->user()->id, $bankAccountId);

        return response()->json([
            'message' => 'Rekening bank berhasil dijadikan default.',
            'data' => new BankAccountResource($account),
        ]);
    }
}
