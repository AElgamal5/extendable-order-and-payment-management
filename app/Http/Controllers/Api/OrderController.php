<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrderAction;
use App\Actions\Orders\DeleteOrderAction;
use App\Actions\Orders\ListOrdersAction;
use App\Actions\Orders\UpdateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\UpdateOrderRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request, ListOrdersAction $action): OrderCollection
    {
        $orders = $action->handle($request->query('status'));

        return new OrderCollection($orders);
    }

    public function store(StoreOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        $order = $action->handle($request->user(), $request->validated());

        return response()->json([
            'message' => 'Order created successfully.',
            'order' => new OrderResource($order),
        ], 201);
    }

    public function show(Order $order): OrderResource
    {
        $order->load('items');

        return new OrderResource($order);
    }

    public function update(UpdateOrderRequest $request, Order $order, UpdateOrderAction $action): JsonResponse
    {
        $order = $action->handle($order, $request->validated());

        return response()->json([
            'message' => 'Order updated successfully.',
            'order' => new OrderResource($order),
        ]);
    }

    public function destroy(Order $order, DeleteOrderAction $action): JsonResponse
    {
        $action->handle($order);

        return response()->json([
            'message' => 'Order deleted successfully.',
        ]);
    }
}
