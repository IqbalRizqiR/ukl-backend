<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\DisputeStatus;
use App\Events\Dispute\DisputeResolved;
use App\Models\Dispute;
use App\Repositories\Contracts\DisputeRepositoryInterface;
use App\Services\Escrow\EscrowService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class DisputeService
{
    public function __construct(
        private readonly DisputeRepositoryInterface $disputeRepository,
        private readonly EscrowService $escrowService,
    ) {}

    /**
     * Get paginated disputes for admin.
     *
     * @param  array{status?: string}  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Dispute::query()
            ->with(['complainant', 'admin', 'order', 'order.buyer', 'order.seller'])
            ->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', DisputeStatus::from($filters['status']));
        }

        return $query->paginate($perPage);
    }

    /**
     * Get dispute details.
     *
     * @param  string  $disputeId
     * @return Dispute
     *
     * @throws RuntimeException
     */
    public function show(string $disputeId): Dispute
    {
        $dispute = $this->disputeRepository->findById($disputeId);

        if (! $dispute) {
            throw new RuntimeException('Komplain tidak ditemukan.');
        }

        return $dispute->load([
            'complainant',
            'admin',
            'order',
            'order.buyer',
            'order.seller',
            'order.product',
            'evidences',
            'evidences.user',
        ]);
    }

    /**
     * Resolve a dispute (refund or release funds).
     *
     * @param  string  $disputeId
     * @param  string  $adminId
     * @param  array{resolution_type: string, admin_notes?: string, resolution?: string}  $data
     * @return Dispute
     *
     * @throws RuntimeException
     */
    public function resolve(string $disputeId, string $adminId, array $data): Dispute
    {
        $dispute = $this->disputeRepository->findById($disputeId);

        if (! $dispute) {
            throw new RuntimeException('Komplain tidak ditemukan.');
        }

        if (! in_array($dispute->status, [DisputeStatus::Open, DisputeStatus::UnderReview], true)) {
            throw new RuntimeException('Komplain sudah diselesaikan.');
        }

        return DB::transaction(function () use ($dispute, $adminId, $data): Dispute {
            $resolutionType = $data['resolution_type'];

            $newStatus = match ($resolutionType) {
                'refund' => DisputeStatus::ResolvedRefund,
                'release' => DisputeStatus::ResolvedNoRefund,
                default => throw new RuntimeException("Tipe resolusi tidak valid: {$resolutionType}"),
            };

            $this->disputeRepository->update($dispute->id, [
                'admin_id' => $adminId,
                'status' => $newStatus,
                'admin_notes' => $data['admin_notes'] ?? null,
                'resolution' => $data['resolution'] ?? null,
                'resolved_at' => now(),
            ]);

            if ($resolutionType === 'refund') {
                $this->escrowService->refund($dispute->order_id);
            } else {
                $this->escrowService->release($dispute->order_id);
            }

            $dispute = $dispute->refresh()->load(['complainant', 'admin', 'order', 'evidences']);

            event(new DisputeResolved($dispute));

            return $dispute;
        });
    }
}
