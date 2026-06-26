<?php

namespace App\Actions\Orders;

use App\Exceptions\OrderHasPaymentsException;
use App\Models\Order;

class DeleteOrderAction
{
    public function handle(Order $order): void
    {
        if ($order->payments()->exists()) {
            throw new OrderHasPaymentsException;
        }

        $order->delete();
    }
}
