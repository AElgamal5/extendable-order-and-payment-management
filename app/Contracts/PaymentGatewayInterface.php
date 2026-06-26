<?php

namespace App\Contracts;

use App\Models\Order;
use App\Payment\PaymentResult;

interface PaymentGatewayInterface
{
    public function process(Order $order, array $data): PaymentResult;

    public function supports(string $method): bool;
}
