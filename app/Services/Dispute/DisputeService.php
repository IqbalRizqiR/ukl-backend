<?php

declare(strict_types=1);

namespace App\Services\Dispute;

use App\Enums\DisputeReason;
use App\Enums\DisputeStatus;
use App\Enums\EvidenceType;
use App\Enums\OrderStatus;
use App\Events\Dispute\DisputeOpened;
use App\Events\Dispute\DisputeResolved;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use App\Repositories\Contracts\DisputeRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\Escrow\EscrowService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class DisputeService
{
    public function __construct(
        private readonly DisputeRepositoryInterface $disputeRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EscrowService $escrowService,
    ) {}

    /**
     * Open a new dispute for an order.
     *
     * @param  string  $orderId
     * @param  string  $complainantId
     * @param  array{reason: DisputeReason|string, description: string, evidences?: array}  $data
     * @return Dispute
     *
     * @throws RuntimeException
     */
    public function open(string $orderId, string $complainantId, array $data): Dispute
    {
        $order = $this->orderRepository->findById($orderId);

        if (! $order) {
            throw new RuntimeException('Pesanan tidak ditemukan.');
        }

        if ($order->buyer_id !== $complainantId) {
            throw new RuntimeException('Hanya pembeli yang dapat mengajukan komplain.');
        }

        if ($order->status !== OrderStatus::Delivered) {
            throw new RuntimeException('Komplain hanya bisa diajukan setelah barang diterima.');
        }

        // Check if dispute already exists for this order
        $existing = $this->disputeRepository->findByOrderId($orderId);
        if ($existing) {
            throw new RuntimeException('Komplain untuk pesanan ini sudah pernah diajukan.');
        }

        $reason = is_string($data['reason'])
            ? DisputeReason::from($data['reason'])
            : $data['reason'];

        return DB::transaction(function () use ($orderId, $complainantId, $reason, $data, $order): Dispute {
            $dispute = $this->disputeRepository->create([
                'order_id' => $orderId,
                'complainant_id' => $complainantId,
                'reason' => $reason,
                'description' => $data['description'],
                'status' => DisputeStatus::Open,
            ]);

            // Upload evidences if provided
            if (! empty($data['evidences'])) {
                foreach ($data['evidences'] as $evidence) {
                    $this->storeEvidence($dispute->id, $complainantId, $evidence);
                }
            }

            // Freeze escrow
            $this->escrowService->freeze($orderId);

            $dispute->load(['complainant', 'order', 'evidences']);

            event(new DisputeOpened($dispute));

            return $dispute;
        });
    }

    /**
     * Admin resolves a dispute.
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
            $resolutionType = $data['resolution_type']; // 'refund' or 'release'

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

            // Execute resolution
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

    /**
     * Add evidence to a dispute.
     *
     * @param  string  $disputeId
     * @param  string  $userId
     * @param  array{type?: EvidenceType|string, image?: UploadedFile, description?: string}  $data
     * @return DisputeEvidence
     */
    public function addEvidence(string $disputeId, string $userId, array $data): DisputeEvidence
    {
        $dispute = $this->disputeRepository->findById($disputeId);

        if (! $dispute) {
            throw new RuntimeException('Komplain tidak ditemukan.');
        }

        if (in_array($dispute->status, [DisputeStatus::ResolvedRefund, DisputeStatus::ResolvedNoRefund, DisputeStatus::Closed], true)) {
            throw new RuntimeException('Tidak bisa menambah bukti untuk komplain yang sudah diselesaikan.');
        }

        return $this->storeEvidence($disputeId, $userId, $data);
    }

    /**
     * Get all open/under-review disputes (admin).
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getOpenDisputes(int $perPage = 15): LengthAwarePaginator
    {
        return Dispute::query()
            ->whereIn('status', [DisputeStatus::Open, DisputeStatus::UnderReview])
            ->with(['complainant', 'order', 'order.buyer', 'order.seller'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Store a single evidence record.
     */
    private function storeEvidence(string $disputeId, string $userId, array $evidence): DisputeEvidence
    {
        $imageUrl = null;

        if (isset($evidence['image']) && $evidence['image'] instanceof UploadedFile) {
            $path = $evidence['image']->store('disputes/evidences', 'public');
            $imageUrl = Storage::disk('public')->url($path);
        }

        $type = isset($evidence['type'])
            ? (is_string($evidence['type']) ? EvidenceType::from($evidence['type']) : $evidence['type'])
            : ($imageUrl ? EvidenceType::Image : EvidenceType::Text);

        return DisputeEvidence::create([
            'dispute_id' => $disputeId,
            'user_id' => $userId,
            'type' => $type,
            'image_url' => $imageUrl,
            'description' => $evidence['description'] ?? null,
        ]);
    }
}
