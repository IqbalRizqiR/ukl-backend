<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class ReportService
{
    /**
     * Get transaction report with summary.
     *
     * @param  array{start_date?: string, end_date?: string}  $filters
     * @return array{total_orders: int, total_revenue: float, total_service_fee: float, completed_orders: int, cancelled_orders: int, orders: \Illuminate\Pagination\LengthAwarePaginator}
     */
    public function transactions(array $filters = []): array
    {
        $startDate = isset($filters['start_date'])
            ? Carbon::parse($filters['start_date'])->startOfDay()
            : now()->subDays(30)->startOfDay();

        $endDate = isset($filters['end_date'])
            ? Carbon::parse($filters['end_date'])->endOfDay()
            : now()->endOfDay();

        $stats = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total_orders,
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COALESCE(SUM(service_fee), 0) as total_service_fee,
                COUNT(CASE WHEN status = ? THEN 1 END) as completed_orders,
                COUNT(CASE WHEN status = ? THEN 1 END) as cancelled_orders
            ', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
            ->first();

        $orders = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['buyer', 'seller', 'product'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return [
            'total_orders' => (int) $stats->total_orders,
            'total_revenue' => (float) $stats->total_revenue,
            'total_service_fee' => (float) $stats->total_service_fee,
            'completed_orders' => (int) $stats->completed_orders,
            'cancelled_orders' => (int) $stats->cancelled_orders,
            'period' => [
                'start' => $startDate->toISOString(),
                'end' => $endDate->toISOString(),
            ],
            'orders' => $orders,
        ];
    }

    /**
     * Get platform overview dashboard stats.
     *
     * @return array{total_users: int, total_sellers: int, verified_sellers: int, total_products: int, active_products: int, total_orders: int, pending_orders: int, total_revenue: float, total_service_fee: float}
     */
    public function overview(): array
    {
        $userStats = User::query()
            ->selectRaw('
                COUNT(*) as total_users,
                COUNT(CASE WHEN is_seller = true THEN 1 END) as total_sellers,
                COUNT(CASE WHEN is_seller_verified = true THEN 1 END) as verified_sellers
            ')
            ->first();

        $productStats = DB::table('products')
            ->whereNull('deleted_at')
            ->selectRaw("
                COUNT(*) as total_products,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_products
            ")
            ->first();

        $orderStats = Order::query()
            ->selectRaw('
                COUNT(*) as total_orders,
                COUNT(CASE WHEN status = ? THEN 1 END) as pending_orders,
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COALESCE(SUM(service_fee), 0) as total_service_fee
            ', [OrderStatus::PendingPayment->value])
            ->first();

        return [
            'total_users' => (int) $userStats->total_users,
            'total_sellers' => (int) $userStats->total_sellers,
            'verified_sellers' => (int) $userStats->verified_sellers,
            'total_products' => (int) $productStats->total_products,
            'active_products' => (int) $productStats->active_products,
            'total_orders' => (int) $orderStats->total_orders,
            'pending_orders' => (int) $orderStats->pending_orders,
            'total_revenue' => (float) $orderStats->total_revenue,
            'total_service_fee' => (float) $orderStats->total_service_fee,
        ];
    }
}
