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
        // I-validate ang format — kinahanglan YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return ['month' => $month, 'total_orders' => 0, 'total_sales' => 0, 'top_products' => []];
        }

        [$year, $mon] = explode('-', $month);

        $orders = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $mon)
            ->where('status', 'served')
            ->get();

        // Top products para sa bulan
        $topProducts = OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_revenue')
            )
            ->with('product:id,name,price')
            ->whereHas('order', function ($q) use ($year, $mon) {
                $q->where('status', 'served')
                  ->whereYear('created_at', $year)
                  ->whereMonth('created_at', $mon);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'month'        => $month,
            'total_orders' => $orders->count(),
            'total_sales'  => $orders->sum('total_amount'),
            'top_products' => $topProducts,
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
