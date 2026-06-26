<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;

class UpdateOrderStatusAction
{
    public function handle(Order $order, OrderStatus $status): Order
    {
        $order->update(['status' => $status->value]);

        return $order->fresh('items');
    }
}
