<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Lohika para sa mga order
class OrderService
{
    // Kuha sa tanan pending/preparing orders
    public function getActiveOrders()
    {
        return Order::with(['items.product', 'cashier', 'table'])
            ->whereIn('status', ['pending', 'preparing'])
            ->latest()
            ->get();
    }

    // Kuha sa history — served ug cancelled orders, paginated
    public function getOrderHistory(string $date = null, int $perPage = 20)
    {
        $query = Order::with(['items.product', 'cashier', 'table'])
            ->whereIn('status', ['served', 'cancelled'])
            ->latest();

        // Filter by date kung gihatag
        if ($date) {
            $query->whereDate('created_at', $date);
        }

        return $query->paginate($perPage);
    }

    // Kuha sa usa ka order gamit ang ID
    public function getOrder(int $id): Order
    {
        return Order::with(['items.product', 'cashier', 'table'])->findOrFail($id);
    }

    // Buhatan ug bag-ong order, i-save sa DB
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // Buhatan ug unique order number — i-retry kung ma-collision
            $orderNumber = $this->generateUniqueOrderNumber();
            $order = Order::create([
                'order_number'    => $orderNumber,
                'cashier_id'      => auth()->id(),
                'table_id'        => $data['table_id'] ?? null,
                'order_type'      => $data['order_type'],
                'status'          => 'pending',
                'discount'        => $data['discount'] ?? 0,
                'amount_tendered' => $data['amount_tendered'] ?? 0,
                'notes'           => $data['notes'] ?? null,
                'total_amount'    => 0,
            ]);

            $total = 0;

            // I-save ang matag item sa order
            foreach ($data['items'] as $item) {
                $subtotal = $item['unit_price'] * $item['quantity'];
                $total += $subtotal;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal'   => $subtotal,
                    'notes'      => $item['notes'] ?? null,
                ]);
            }

            // I-update ang total — i-cap ang discount para dili molapas sa total
            $discount = min($order->discount, $total);
            $order->update([
                'discount'     => $discount,
                'total_amount' => max(0, $total - $discount),
                'change'       => max(0, $order->amount_tendered - max(0, $total - $discount)),
            ]);

            // I-mark ang mesa nga occupied kung dine-in
            if ($order->table_id) {
                // I-check kung available ba ang mesa
                $table = Table::findOrFail($order->table_id);
                if ($table->status === 'reserved') {
                    abort(422, 'Table is reserved and cannot be used for a new order.');
                }
                $table->update(['status' => 'occupied']);
            }

            return $order->load(['items.product', 'cashier', 'table']);
        });
    }

    // I-update ang status (e.g. pending → preparing → served)
    public function updateStatus(int $id, string $status, array $data = []): Order
    {
        $order = Order::findOrFail($id);

        // Valid nga transitions lang ang pwede — dili pwede mobalik
        $allowed = [
            'pending'    => ['preparing', 'cancelled'],
            'preparing'  => ['served', 'cancelled'],
            'served'     => [],
            'cancelled'  => [],
        ];

        if (!in_array($status, $allowed[$order->status] ?? [])) {
            abort(422, "Cannot transition from '{$order->status}' to '{$status}'.");
        }

        $updates = ['status' => $status];

        // Kung gi-serve na, i-record ang bayad ug i-compute ang sukli
        if ($status === 'served' && isset($data['amount_tendered'])) {
            $updates['amount_tendered'] = $data['amount_tendered'];
            $updates['change'] = max(0, $data['amount_tendered'] - $order->total_amount);
        }

        $order->update($updates);

        // Kung served o cancelled, i-free ang mesa
        if (in_array($status, ['served', 'cancelled']) && $order->table_id) {
            $this->freeTableIfNoActiveOrders($order->table_id);
        }

        return $order->load(['items.product', 'cashier', 'table']);
    }

    // I-cancel ang order
    public function cancelOrder(int $id): void
    {
        $order = Order::findOrFail($id);

        // Pending ug preparing lang ang pwede i-cancel
        if (!in_array($order->status, ['pending', 'preparing'])) {
            abort(422, 'Only pending or preparing orders can be cancelled.');
        }

        $order->update(['status' => 'cancelled']);

        // I-free ang mesa kung walay active orders na
        if ($order->table_id) {
            $this->freeTableIfNoActiveOrders($order->table_id);
        }
    }

    // I-check kung naa pay active orders sa mesa — kung wala, i-set back to available
    private function freeTableIfNoActiveOrders(int $tableId): void
    {
        $hasActive = Order::where('table_id', $tableId)
            ->whereIn('status', ['pending', 'preparing'])
            ->exists();

        if (!$hasActive) {
            Table::where('id', $tableId)->update(['status' => 'available']);
        }
    }

    // Buhatan ug unique order number — i-retry kung ma-collision (max 5 tries)
    private function generateUniqueOrderNumber(): string
    {
        $attempts = 0;
        do {
            $number = 'ORD-' . strtoupper(Str::random(6));
            $exists = Order::where('order_number', $number)->exists();
            $attempts++;
        } while ($exists && $attempts < 5);

        return $number;
    }
}
