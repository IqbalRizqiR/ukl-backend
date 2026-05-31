<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Dispute\DisputeResource;
use App\Services\Admin\DisputeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class DisputeController extends Controller
{
    public function __construct(
        private readonly DisputeService $disputeService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $disputes = $this->disputeService->list($request->all());

        return DisputeResource::collection($disputes);
    }

    public function show(string $disputeId): DisputeResource
    {
        $dispute = $this->disputeService->show($disputeId);

        return new DisputeResource($dispute);
    }

    public function resolve(Request $request, string $disputeId): JsonResponse
    {
        $request->validate([
            'resolution_type' => ['required', 'string', 'in:refund,release'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'resolution' => ['nullable', 'string', 'max:2000'],
        ]);

        $dispute = $this->disputeService->resolve(
            $disputeId,
            $request->user()->id,
            $request->only('resolution_type', 'admin_notes', 'resolution'),
        );

        return response()->json([
            'message' => 'Sengketa berhasil diselesaikan.',
            'data' => new DisputeResource($dispute),
        ]);
    }
}
