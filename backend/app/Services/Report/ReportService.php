<?php

namespace App\Services\Report;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

// Lohika sa pag-generate sa mga report
class ReportService
{
    // Kuha sa total sales karong adlaw
    public function getDailySales(string $date): array
    {
        $orders = Order::whereDate('created_at', $date)
            ->where('status', 'served')
            ->get();

        return [
            'date'         => $date,
            'total_orders' => $orders->count(),
            'total_sales'  => $orders->sum('total_amount'),
            'total_discount' => $orders->sum('discount'),
        ];
    }

    // Kuha sa total sales sa usa ka bulan
    public function getMonthlySales(string $month): array
    {
        [$year, $mon] = explode('-', $month);

        $orders = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $mon)
            ->where('status', 'served')
            ->get();

        return [
            'month'        => $month,
            'total_orders' => $orders->count(),
            'total_sales'  => $orders->sum('total_amount'),
        ];
    }

    // Asa mga produkto ang lami kaayo kabaligya, optional date filter
    public function getTopProducts(int $limit, string $date = null): array
    {
        $query = OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->with('product:id,name,price')
            ->whereHas('order', function ($q) use ($date) {
                $q->where('status', 'served');
                // I-filter by date kung gihatag
                if ($date) {
                    $q->whereDate('created_at', $date);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit($limit);

        return $query->get()->toArray();
    }
}
