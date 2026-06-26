<?php

namespace App\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Payment\PaymentResult;

class PayPalGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly array $config,
    ) {}

    public function process(Order $order, array $data): PaymentResult
    {
        $transactionId = 'pp_'.str()->uuid();

        return new PaymentResult(
            success: true,
            transactionId: $transactionId,
            message: 'PayPal payment processed successfully.',
            gatewayUsed: 'paypal',
        );
    }

    public function supports(string $method): bool
    {
        return $method === 'paypal';
    }
}
