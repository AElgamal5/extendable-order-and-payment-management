<?php

namespace App\Actions\Orders;

use App\Models\Order;
use App\Models\User;

class CreateOrderAction
{
    public function handle(User $user, array $data): Order
    {
        $total = collect($data['items'])->sum(fn ($item) => $item['quantity'] * $item['unit_price']);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'total' => $total,
        ]);

        $items = collect($data['items'])->map(fn ($item) => [
            'product_name' => $item['product_name'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'subtotal' => $item['quantity'] * $item['unit_price'],
        ]);

        $order->items()->createMany($items->toArray());

        return $order->fresh('items');
    }
}
