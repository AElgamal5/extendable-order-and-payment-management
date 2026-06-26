<?php

namespace App\Actions\Orders;

use App\Models\Order;

class UpdateOrderAction
{
    public function handle(Order $order, array $data): Order
    {
        if (!isset($data['items'])) {
            return $order;
        }

        $total = collect($data['items'])->sum(fn($item) => $item['quantity'] * $item['unit_price']);

        $items = collect($data['items'])->map(fn($item) => [
            'product_name' => $item['product_name'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'subtotal' => $item['quantity'] * $item['unit_price'],
        ]);

        $order->items()->delete();
        $order->items()->createMany($items->toArray());
        $order->update(['total' => $total]);

        return $order->fresh('items');
    }
}
