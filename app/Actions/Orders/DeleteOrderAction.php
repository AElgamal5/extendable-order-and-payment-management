<?php

namespace App\Actions\Orders;

use App\Models\Order;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class DeleteOrderAction
{
    public function handle(Order $order): void
    {
        // if ($order->payments()->exists()) {
        //     throw new ConflictHttpException('Cannot delete order with associated payments.');
        // }

        $order->delete();
    }
}
