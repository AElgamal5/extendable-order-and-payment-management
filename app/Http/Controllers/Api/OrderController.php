<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\DeleteOrderAction;
use App\Actions\Orders\ListOrdersAction;
use App\Actions\Orders\UpdateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request, ListOrdersAction $action): JsonResponse
    {
        $orders = $action->handle($request->query('status'));

        return ApiResponse::success(
            data: OrderResource::collection($orders),
            message: 'Orders retrieved successfully.',
            meta: [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        );
    }

    public function store(StoreOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        $order = $action->handle($request->user(), $request->validated());

        return ApiResponse::created([
            'order' => new OrderResource($order),
        ], 'Order created successfully.');
    }

    public function show(Order $order): JsonResponse
    {
        $order->load('items');

        return ApiResponse::success([
            'order' => new OrderResource($order),
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order, UpdateOrderAction $action): JsonResponse
    {
        $order = $action->handle($order, $request->validated());

        return ApiResponse::success([
            'order' => new OrderResource($order),
        ], 'Order updated successfully.');
    }

    public function destroy(Order $order, DeleteOrderAction $action): JsonResponse
    {
        $action->handle($order);

        return ApiResponse::ok('Order deleted successfully.');
    }
}
