<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Ari ang pag-manage sa mga order
class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    // Tanan aktibong order
    public function index(): JsonResponse
    {
        return response()->json(
            OrderResource::collection($this->orderService->getActiveOrders())
        );
    }

    // Order history — served ug cancelled, pwede filter by date
    public function history(Request $request): JsonResponse
    {
        $date    = $request->query('date');
        $history = $this->orderService->getOrderHistory($date);

        return response()->json([
            'data'         => OrderResource::collection($history->items()),
            'current_page' => $history->currentPage(),
            'last_page'    => $history->lastPage(),
            'total'        => $history->total(),
        ]);
    }

    // Bag-ong order gikan sa cashier
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->validated());
        return response()->json(new OrderResource($order), 201);
    }

    // Tan-awa ang usa ka order
    public function show(int $id): JsonResponse
    {
        return response()->json(
            new OrderResource($this->orderService->getOrder($id))
        );
    }

    // I-update ang status sa order (preparing, served, etc.)
    public function updateStatus(int $id, string $status, UpdateOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->updateStatus($id, $status, $request->validated());
        return response()->json(new OrderResource($order));
    }

    // I-cancel ang order
    public function cancel(int $id): JsonResponse
    {
        $this->orderService->cancelOrder($id);
        return response()->json(['message' => 'Order cancelled successfully.']);
    }
}
