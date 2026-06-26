<?php

namespace App\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Payment\PaymentResult;

class CreditCardGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly array $config,
    ) {}

    public function process(Order $order, array $data): PaymentResult
    {
        $transactionId = 'cc_'.str()->uuid();

        return new PaymentResult(
            success: true,
            transactionId: $transactionId,
            message: 'Credit card payment processed successfully.',
            gatewayUsed: 'credit_card',
        );
    }

    public function supports(string $method): bool
    {
        return $method === 'credit_card';
    }
}
